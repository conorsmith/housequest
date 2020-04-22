<?php
declare(strict_types=1);

namespace App\Domain;

use App\Repositories\ItemRepository;

final class InventoryTreeFactory
{
    /** @var ItemRepository */
    private $itemRepo;

    public function __construct(ItemRepository $itemRepo)
    {
        $this->itemRepo = $itemRepo;
    }

    public function fromInventory(Inventory $inventory): InventoryTreeNode
    {
        $children = [];

        /** @var Item $item */
        foreach ($inventory->getItems() as $item) {

            $childInventory = $this->itemRepo->findInventory(
                ItemWhereabouts::itemSurface($item->getId()->toString())
            );

            if (!$childInventory->isEmpty()) {
                $children[] = $this->fromInventory($childInventory);
            }
        }

        return new InventoryTreeNode($inventory, $children);
    }
}
