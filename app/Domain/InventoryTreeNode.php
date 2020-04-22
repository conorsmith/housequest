<?php
declare(strict_types=1);

namespace App\Domain;

final class InventoryTreeNode
{
    /** @var Inventory */
    private $inventory;

    /** @var array */
    private $children;

    public function __construct(Inventory $inventory, array $children)
    {
        $this->inventory = $inventory;
        $this->children = $children;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function findChild(Item $item): ?self
    {
        /** @var self $child */
        foreach ($this->children as $child) {
            if ($child->getInventory()->isForItem($item)) {
                return $child;
            }
        }

        return null;
    }
}
