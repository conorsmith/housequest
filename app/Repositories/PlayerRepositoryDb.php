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

        $eatenItemTypes = [];

        foreach ($itemActionLogRows as $itemActionLogRow) {
            if ($itemActionLogRow->action === "eat") {
                $eatenItemTypes[] = $itemActionLogRow->item_type_id;
            }
        }

        return new Player(
            Uuid::fromString($row->id),
            $row->location_id,
            intval($row->xp),
            $eatenItemTypes,
            intval($row->eaten_items_count)
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
    }
}
