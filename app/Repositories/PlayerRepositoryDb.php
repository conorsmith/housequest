<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Event;
use App\Domain\Player;
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

        $eatenItemTypes = [];
        $enteredLocations = [];
        $events = [];
        $achievements = [];

        foreach ($itemActionLogRows as $itemActionLogRow) {
            if ($itemActionLogRow->action === "eat") {
                $eatenItemTypes[] = $itemActionLogRow->item_type_id;
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
            $events,
            $achievements,
            $eatenItemTypes,
            intval($row->eaten_items_count),
            $enteredLocations
        );
    }

    public function save(Player $player): void
    {
        DB::table("players")
            ->where([
                'id' => $player->getId(),
            ])
            ->update([
                'location_id'       => $player->getLocationId(),
                'xp'                => $player->getXp(),
                'is_dead'           => $player->isDead(),
                'has_won'           => $player->hasWon(),
                'eaten_items_count' => $player->getEatenItemsCount(),
            ]);

        foreach ($player->getEatenItemTypes() as $itemType) {
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

        foreach ($player->getEnteredLocations() as $locationId) {
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
}
