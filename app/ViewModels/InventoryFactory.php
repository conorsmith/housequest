<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\InventoryTreeNode;
use App\Domain\Item;

final class InventoryFactory
{
    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        ItemFactory $itemViewModelFactory
    ) {
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function fromInventoryTree(InventoryTreeNode $inventoryTree): array
    {
        return $this->createInventory($inventoryTree, 0);
    }

    private function createInventory(?InventoryTreeNode $inventoryTree, int $depth): array
    {
        if (is_null($inventoryTree)) {
            return [];
        }

        $inventoryViewModel = [];

        /** @var Item $item */
        foreach ($inventoryTree->getInventory()->getItems() as $item) {
            $itemViewModel = $this->itemViewModelFactory->create($item);
            $itemViewModel->depth = $depth;
            $itemViewModel->surface = $this->createInventory($inventoryTree->findSurfaceNode($item), $depth + 1);
            $itemViewModel->contents = $this->createInventory($inventoryTree->findContentsNode($item), $depth + 1);
            $itemViewModel->whereabouts = (object) [
                'id' => $item->getWhereabouts()->getId(),
                'type' => $item->getWhereabouts()->getType(),
            ];
            $inventoryViewModel[] = $itemViewModel;
        }

        return $inventoryViewModel;
    }
}
