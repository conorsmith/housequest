<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory;
use App\Domain\Item;
use Ramsey\Uuid\UuidInterface;

interface ItemRepository
{
    public function find(UuidInterface $id): ?Item;
    public function findInventory(string $locationId): Inventory;
    public function createType(string $itemTypeId): Item;
    public function save(Item $item): void;
}
