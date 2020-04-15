<?php
declare(strict_types=1);

namespace App\Repositories;

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

        $eatenItemTypes = [];
        $enteredLocations = [];

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

        return new Player(
            Uuid::fromString($row->id),
            $row->location_id,
            intval($row->xp),
            $row->is_dead === 1,
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
    }
}
