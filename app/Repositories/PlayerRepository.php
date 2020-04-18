<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Player;
use Ramsey\Uuid\UuidInterface;

interface PlayerRepository
{
    public function find(UuidInterface $gameId): Player;
    public function save(Player $player): void;
    public function saveNew(UuidInterface $gameId, Player $player): void;
}
