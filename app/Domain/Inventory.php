<?php
declare(strict_types=1);

namespace App\Domain;

use DomainException;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final class Inventory
{
    /** @var string */
    private $locationId;

    /** @var array */
    private $items;

    public function __construct(string $locationId, array $items)
    {
        $this->locationId = $locationId;
        $this->items = $items;
    }

    public function removeEatenItem(UuidInterface $itemId): void
    {
        $item = $this->find($itemId);

        if (is_null($item)) {
            throw new InvalidArgumentException("Item '{$itemId}' not found in inventory.");
        }

        if (!$item->isEdible()) {
            throw new DomainException("Item type '{$item->getTypeId()}' is not edible");
        }

        if ($item->isSinglePortionItem()) {
            $item->decrementQuantity();
            return;
        }

        $this->removePortionsFromItem($item, 1);
    }

    public function removePortionsFromItem(Item $item, int $portions): void
    {
        if ($item->hasManyQuantities()) {
            $item = $item->split(1);
            $this->items[] = $item;
        }

        $item->removePortions($portions);

        /** @var Item $inventoryItem */
        foreach ($this->items as $inventoryItem) {
            if ($item->canMergeWith($inventoryItem)) {
                $inventoryItem->merge($item);
            }
        }
    }

    public function removeByType(string $itemTypeId): void
    {
        /** @var Item $inventoryItem */
        foreach ($this->items as $inventoryItem) {
            if ($inventoryItem->getTypeId() === $itemTypeId) {
                $inventoryItem->reduceQuantityTo(0);
            }
        }
    }

    public function add(Item $item): void
    {
        $item->moveTo($this->locationId);
        $this->items[] = $item;

        /** @var Item $inventoryItem */
        foreach ($this->items as $inventoryItem) {
            if ($item->canMergeWith($inventoryItem)) {
                $inventoryItem->merge($item);
            }
        }
    }

    public function remove(UuidInterface $id): Item
    {
        /** @var Item $inventoryItem */
        foreach ($this->items as $key => $inventoryItem) {
            if ($inventoryItem->getId()->equals($id)) {
                $item = $this->items[$key];
                unset($this->items[$key]);
                return $item;
            }
        }
    }

    public function find(UuidInterface $id): ?Item
    {
        /** @var Item $item */
        foreach ($this->items as $item) {
            if ($item->getId()->equals($id)) {
                return $item;
            }
        }

        return null;
    }

    public function findStackable(Item $item): ?Item
    {
        /** @var Item $inventoryItem */
        foreach ($this->items as $inventoryItem) {
            if ($inventoryItem->getTypeId() === $item->getTypeId()
                && $inventoryItem->getRemainingPortions() === $item->getRemainingPortions()
            ) {
                return $inventoryItem;
            }
        }

        return null;
    }

    public function hasItemType(string $itemTypeId): bool
    {
        /** @var Item $inventoryItem */
        foreach ($this->items as $inventoryItem) {
            if ($inventoryItem->getTypeId() === $itemTypeId) {
                return true;
            }
        }

        return false;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
