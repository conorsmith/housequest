<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory;
use App\Domain\Item;

interface ItemRepository
{
    public function find(string $id): ?Item;
    public function findInventory(string $locationId): Inventory;
    public function createForInventory(string $itemId): Item;
    public function save(Item $item): void;
}
