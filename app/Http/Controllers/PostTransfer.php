<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostTransfer extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    public function __construct(PlayerRepository $playerRepo, ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(Request $request, string $gameId, string $containerId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $itemIdsFromContainerToPlayer = $request->input("containerItems", []);
        $itemIdsFromPlayerToContainer = $request->input("inventoryItems", []);

        $playerInventory = $itemRepo->findInventory(ItemWhereabouts::player());
        $containerInventory = $itemRepo->findInventory(ItemWhereabouts::itemContents($containerId));

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

        return redirect("/{$gameId}");
    }
}
