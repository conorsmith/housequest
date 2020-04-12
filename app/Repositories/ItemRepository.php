<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Item;

interface ItemRepository
{
    public function find(string $id): ?Item;
    public function findInInventory(Item $item): ?Item;
    public function findInContainer(string $containerId, Item $item): ?Item;
    public function findOneOfType(string $typeId): ?Item;
    public function getInventory(): array;
    public function findAtLocation(string $locationId): array;
    public function findAllInContainer(Item $container): array;
    public function findMultiple(array $ids): array;
    public function createForInventory(string $itemId): Item;
    public function save(Item $item): void;
}
