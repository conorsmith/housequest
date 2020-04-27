<?php
declare(strict_types=1);

namespace App\Domain;

final class InventoryTreeNode
{
    /** @var Inventory */
    private $inventory;

    /** @var array */
    private $surfaceNodes;

    public function __construct(Inventory $inventory, array $surfaceNodes)
    {
        $this->inventory = $inventory;
        $this->surfaceNodes = $surfaceNodes;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getSurfaceNodes(): array
    {
        return $this->surfaceNodes;
    }

    public function findSurfaceNode(Item $item): ?self
    {
        /** @var self $surfaceNode */
        foreach ($this->surfaceNodes as $surfaceNode) {
            if ($surfaceNode->getInventory()->isForItem($item)) {
                return $surfaceNode;
            }
        }

        return null;
    }
}
