<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostTransfer extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(Request $request, string $gameId, string $containerId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $itemIdsFromContainerToPlayer = $request->input("containerItems", []);
        $itemIdsFromPlayerToContainer = $request->input("inventoryItems", []);

        $playerInventory = new Inventory("player", $itemRepo->getInventory());
        $containerInventory = new Inventory($containerId, $itemRepo->findAtLocation($containerId));

        /** @var Item $fromItem */
        foreach ($containerInventory->getItems() as $fromItem) {
            if (array_key_exists($fromItem->getId()->toString(), $itemIdsFromContainerToPlayer)) {
                $quantity = intval($itemIdsFromContainerToPlayer[$fromItem->getId()->toString()]);
                $fromItem->removeQuantity($quantity);

                $toItem = $playerInventory->findStackable($fromItem);
                if (is_null($toItem)) {
                    $toItem = $fromItem->createEmptyCopy();
                    $playerInventory->add($toItem);
                }
                $toItem->addQuantity($quantity);
            }
        }

        /** @var Item $fromItem */
        foreach ($playerInventory->getItems() as $fromItem) {
            if (array_key_exists($fromItem->getId()->toString(), $itemIdsFromPlayerToContainer)) {
                $quantity = intval($itemIdsFromPlayerToContainer[$fromItem->getId()->toString()]);
                $fromItem->removeQuantity($quantity);

                $toItem = $containerInventory->findStackable($fromItem);
                if (is_null($toItem)) {
                    $toItem = $fromItem->createEmptyCopy();
                    $containerInventory->add($toItem);
                }
                $toItem->addQuantity($quantity);
            }
        }

        /** @var Item $inventoryItem */
        foreach ($containerInventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        /** @var Item $inventoryItem */
        foreach ($playerInventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        /*
        foreach ($itemIdsFromContainerToInventory as $itemId => $quantity) {
            $itemFrom = $itemRepo->find($itemId);
            $itemFrom->removeQuantity(intval($quantity));
            $itemRepo->save($itemFrom);

            $itemTo = $itemRepo->findInInventory($itemFrom);
            if (is_null($itemTo)) {
                $itemTo = $itemFrom->copyTo("player");
            }
            $itemTo->addQuantity(intval($quantity));
            $itemRepo->save($itemTo);
        }

        foreach ($itemIdsFromInventoryToContainer as $itemId => $quantity) {
            $itemFrom = $itemRepo->find($itemId);
            $itemFrom->removeQuantity(intval($quantity));
            $itemRepo->save($itemFrom);

            $itemTo = $itemRepo->findInContainer($containerId, $itemFrom);
            if (is_null($itemTo)) {
                $itemTo = $itemFrom->copyTo($containerId);
            }
            $itemTo->addQuantity(intval($quantity));
            $itemRepo->save($itemTo);
        }
        */

        return redirect("/{$gameId}");
    }
}
