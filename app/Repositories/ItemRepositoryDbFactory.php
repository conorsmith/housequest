<?php
declare(strict_types=1);

namespace App\Repositories;

use Ramsey\Uuid\UuidInterface;

final class ItemRepositoryDbFactory
{
    /** @var array */
    private $itemConfig;

    public function __construct(array $itemConfig)
    {
        $this->itemConfig = $itemConfig;
    }

    public function create(UuidInterface $gameId): ItemRepositoryDb
    {
        return new ItemRepositoryDb($gameId, $this->itemConfig);
    }
}
