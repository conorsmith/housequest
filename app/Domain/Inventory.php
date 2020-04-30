<?php
declare(strict_types=1);

namespace App\Domain;

use DomainException;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final class Inventory
{
    /** @var ItemWhereabouts */
    private $whereabouts;

    /** @var array */
    private $items;

    public function __construct(ItemWhereabouts $whereabouts, array $items)
    {
        $this->whereabouts = $whereabouts;
        $this->items = $items;
    }

    public function removeExpendedItem(UuidInterface $itemId): void
    {
        $item = $this->find($itemId);

        if (is_null($item)) {
            throw new InvalidArgumentException("Item '{$itemId}' not found in inventory.");
        }

        if (!$item->isExhaustible()) {
            throw new DomainException("Item type '{$item->getTypeId()}' cannot be used.");
        }

        if ($item->isSinglePortionItem()) {
            $item->decrementQuantity();
            return;
        }

        $this->removePortionsFromItem($item, 1);
    }

    public function removeEatenItem(UuidInterface $itemId): void
    {
        $item = $this->find($itemId);

        if (is_null($item)) {
            throw new InvalidArgumentException("Item '{$itemId}' not found in inventory.");
        }

        if (!$item->isIngestible()) {
            throw new DomainException("Item type '{$item->getTypeId()}' cannot be eaten.");
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
        $item->moveTo($this->whereabouts);
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

    public function isForItem(Item $item): bool
    {
        return $this->whereabouts->isForItem($item);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }
}
