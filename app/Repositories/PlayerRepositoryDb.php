<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Player;
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

        return new Player(
            Uuid::fromString($row->id),
            $row->location_id,
            intval($row->xp)
        );
    }

    public function save(Player $player): void
    {
        DB::table("players")
            ->where([
                'id' => $player->getId(),
            ])
            ->update([
                'location_id' => $player->getLocationId(),
                'xp'          => $player->getXp(),
            ]);
    }
}
