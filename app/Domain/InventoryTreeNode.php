<?php
declare(strict_types=1);

namespace App\Domain;

final class InventoryTreeNode
{
    /** @var Inventory */
    private $inventory;

    /** @var array */
    private $surfaceNodes;

    /** @var array */
    private $contentsNodes;

    public function __construct(Inventory $inventory, array $surfaceNodes, array $contentsNodes)
    {
        $this->inventory = $inventory;
        $this->surfaceNodes = $surfaceNodes;
        $this->contentsNodes = $contentsNodes;
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

    public function findContentsNode(Item $item): ?self
    {
        /** @var self $contentsNode */
        foreach ($this->contentsNodes as $contentsNode) {
            if ($contentsNode->getInventory()->isForItem($item)) {
                return $contentsNode;
            }
        }

        return null;
    }
}
