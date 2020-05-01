<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Event;
use App\Domain\Player;
use App\Domain\PlayerStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerRepositoryDb implements PlayerRepository
{
    public function find(UuidInterface $gameId): Player
    {
        $row = DB::selectOne("SELECT * FROM players WHERE game_id = ?", [
            $gameId
        ]);

        $itemActionLogRows = DB::select("SELECT * FROM player_item_action_log WHERE player_id = ?", [
            $row->id,
        ]);

        $locationActionLogRows = DB::select("SELECT * FROM player_location_action_log WHERE player_id = ?", [
            $row->id,
        ]);

        $eventLogRows = DB::select("SELECT * FROM player_event_log WHERE player_id = ? ORDER BY created_at DESC", [
            $row->id,
        ]);

        $achievementRows = DB::select("SELECT * FROM achievements WHERE player_id = ?", [
            $row->id,
        ]);

        $usedItemTypes = [];
        $usedItemComboTypes = [];
        $eatenItemTypes = [];
        $enteredLocations = [];
        $events = [];
        $achievements = [];

        foreach ($itemActionLogRows as $itemActionLogRow) {
            if ($itemActionLogRow->action === "eat") {
                $eatenItemTypes[] = $itemActionLogRow->item_type_id;
            } elseif ($itemActionLogRow->action === "use") {
                $usedItemTypes[] = $itemActionLogRow->item_type_id;
            } elseif ($itemActionLogRow->action === "use-with") {
                $usedItemComboTypes[] = explode("|", $itemActionLogRow->item_type_id);
            }
        }

        foreach ($locationActionLogRows as $locationActionLogRow) {
            if ($locationActionLogRow->action === "entered") {
                $enteredLocations[] = $locationActionLogRow->location_id;
            }
        }

        foreach ($eventLogRows as $eventLogRow) {
            $events[] = new Event($eventLogRow->event_id, $eventLogRow->location_id);
        }

        foreach ($achievementRows as $achievementRow) {
            $achievements[] = $achievementRow->achievement_id;
        }

        return new Player(
            Uuid::fromString($row->id),
            $row->name,
            $row->location_id,
            intval($row->xp),
            $row->is_dead === 1,
            $row->has_won === 1,
            json_decode($row->conditions, true) ?? [],
            $events,
            $achievements,
            $eatenItemTypes,
            intval($row->eaten_items_count),
            $enteredLocations,
            new PlayerStats(
                $usedItemTypes,
                intval($row->used_items_count),
                $usedItemComboTypes,
                intval($row->used_item_combos_count),
                $eatenItemTypes,
                intval($row->eaten_items_count),
                $enteredLocations
            )
        );
    }

    public function save(Player $player): void
    {
        DB::table("players")
            ->where([
                'id' => $player->getId(),
            ])
            ->update([
                'location_id'            => $player->getLocationId(),
                'xp'                     => $player->getXp(),
                'is_dead'                => $player->isDead(),
                'has_won'                => $player->hasWon(),
                'conditions'             => json_encode($player->getConditions()),
                'used_items_count'       => $player->getStats()->getUsedItemsCount(),
                'used_item_combos_count' => $player->getStats()->getUsedItemCombosCount(),
                'eaten_items_count'      => $player->getStats()->getEatenItemsCount(),
            ]);

        foreach ($player->getStats()->getUsedItemTypes() as $itemType) {
            DB::table("player_item_action_log")
                ->updateOrInsert(
                    [
                        'player_id'    => $player->getId(),
                        'item_type_id' => $itemType,
                        'action'       => "use",
                    ],
                    [
                        'id'           => Uuid::uuid4(),
                        'created_at'   => Carbon::now("Europe/Dublin"),
                    ]
                );
        }

        foreach ($player->getStats()->getUsedItemCombos() as $itemTypes) {
            DB::table("player_item_action_log")
                ->updateOrInsert(
                    [
                        'player_id'    => $player->getId(),
                        'item_type_id' => implode("|", $itemTypes),
                        'action'       => "use-with",
                    ],
                    [
                        'id'           => Uuid::uuid4(),
                        'created_at'   => Carbon::now("Europe/Dublin"),
                    ]
                );
        }

        foreach ($player->getStats()->getEatenItemTypes() as $itemType) {
            DB::table("player_item_action_log")
                ->updateOrInsert(
                    [
                        'player_id'    => $player->getId(),
                        'item_type_id' => $itemType,
                        'action'       => "eat",
                    ],
                    [
                        'id'           => Uuid::uuid4(),
                        'created_at'   => Carbon::now("Europe/Dublin"),
                    ]
                );
        }

        foreach ($player->getStats()->getEnteredLocations() as $locationId) {
            DB::table("player_location_action_log")
                ->updateOrInsert(
                    [
                        'player_id'   => $player->getId(),
                        'location_id' => $locationId,
                        'action'      => "entered",
                    ],
                    [
                        'id'           => Uuid::uuid4(),
                        'created_at'   => Carbon::now("Europe/Dublin"),
                    ]
                );
        }

        $eventRows = DB::select("SELECT * FROM player_event_log WHERE player_id = :player_id", [
            'player_id' => $player->getId(),
        ]);

        $eventIds = [];

        foreach ($eventRows as $eventRow) {
            $eventIds[] = $eventRow->event_id;
        }

        /** @var Event $event */
        foreach ($player->getEvents() as $event) {
            if (!in_array($event->getId(), $eventIds)) {
                DB::table("player_event_log")
                    ->insert([
                        'id'          => Uuid::uuid4(),
                        'player_id'   => $player->getId(),
                        'event_id'    => $event->getId(),
                        'location_id' => $event->getLocationId(),
                        'created_at'  => Carbon::now("Europe/Dublin"),
                    ]);
            }
        }

        $achievementRows = DB::select("SELECT * FROM achievements WHERE player_id = :player_id", [
            'player_id' => $player->getId(),
        ]);

        $achievementIds = [];

        foreach ($achievementRows as $achievementRow) {
            $achievementIds[] = $achievementRow->achievement_id;
        }

        foreach ($player->getAchievements() as $achievement) {
            if (!in_array($achievement, $achievementIds)) {
                DB::table("achievements")
                    ->insert([
                        'id'             => Uuid::uuid4(),
                        'player_id'      => $player->getId(),
                        'achievement_id' => $achievement,
                        'created_at'     => Carbon::now("Europe/Dublin"),
                    ]);
            }
        }
    }

    public function saveNew(UuidInterface $gameId, Player $player): void
    {
        DB::table("players")->insert([
            'id'                     => $player->getId()->toString(),
            'game_id'                => $gameId->toString(),
            'name'                   => $player->getName(),
            'location_id'            => $player->getLocationId(),
            'xp'                     => 0,
            'is_dead'                => $player->isDead(),
            'has_won'                => $player->hasWon(),
            'conditions'             => json_encode($player->getConditions()),
            'used_items_count'       => $player->getStats()->getUsedItemsCount(),
            'used_item_combos_count' => $player->getStats()->getUsedItemCombosCount(),
            'eaten_items_count'      => $player->getStats()->getEatenItemsCount(),
            'created_at'             => Carbon::now("Europe/Dublin"),
        ]);

        /** @var string $locationId */
        foreach ($player->getStats()->getEnteredLocations() as $locationId) {
            DB::table("player_location_action_log")
                ->insert([
                    'id'          => Uuid::uuid4(),
                    'player_id'   => $player->getId(),
                    'location_id' => $locationId,
                    'action'      => "entered",
                    'created_at'  => Carbon::now("Europe/Dublin"),
                ]);
        }

        /** @var Event $event */
        foreach ($player->getEvents() as $event) {
            DB::table("player_event_log")
                ->insert([
                    'id'          => Uuid::uuid4(),
                    'player_id'   => $player->getId(),
                    'event_id'    => $event->getId(),
                    'location_id' => $event->getLocationId(),
                    'created_at'  => Carbon::now("Europe/Dublin"),
                ]);
        }
    }
}
