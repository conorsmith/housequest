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
        $surfaceNodes = [];
        $contentsNodes = [];

        /** @var Item $item */
        foreach ($inventory->getItems() as $item) {

            $surfaceInventory = $this->itemRepo->findInventory(
                ItemWhereabouts::itemSurface($item->getId()->toString())
            );

            if (!$surfaceInventory->isEmpty()) {
                $surfaceNodes[] = $this->fromInventory($surfaceInventory);
            }

            $contentsInventory = $this->itemRepo->findInventory(
                ItemWhereabouts::itemContents($item->getId()->toString())
            );

            if (!$contentsInventory->isEmpty()) {
                $contentsNodes[] = $this->fromInventory($contentsInventory);
            }
        }

        return new InventoryTreeNode($inventory, $surfaceNodes, $contentsNodes);
    }
}
