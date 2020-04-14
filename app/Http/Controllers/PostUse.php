<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use Illuminate\Validation\Rules\In;
use Ramsey\Uuid\Uuid;

final class PostUse extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory, PlayerRepositoryDb $playerRepo)
    {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
    }

    public function __invoke(string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));
        $item = $itemRepo->find($itemId);

        if ($item->getTypeId() === "step-ladder") {
            $ladderInventory = new Inventory($item->getLocationId(), $itemRepo->findAtLocation($item->getLocationId()));
            if ($item->getLocationId() !== $player->getLocationId()) {
                $locationInventory = new Inventory($player->getLocationId(), $itemRepo->findAtLocation($player->getLocationId()));
            } else {
                $locationInventory = $ladderInventory;
            }

            /** @var Item $inventoryItem */
            foreach ($ladderInventory->getItems() as $inventoryItem) {
                if ($inventoryItem->getId()->equals($item->getId())) {
                    $inventoryItem->decrementQuantity();
                }
            }

            $item = $itemRepo->createForInventory("deployed-step-ladder");
            $item->moveTo($player->getLocationId());
            $item->incrementQuantity();
            $locationInventory->add($item);

            /** @var Item $inventoryItem */
            foreach ($ladderInventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }

            /** @var Item $inventoryItem */
            foreach ($locationInventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }

            session()->flash("success", "You deployed the Step Ladder.");
            return redirect("/{$gameId}");
        }

        if ($item->getTypeId() === "deployed-step-ladder") {
            $inventory = new Inventory($item->getLocationId(), $itemRepo->findAtLocation($item->getLocationId()));

            /** @var Item $inventoryItem */
            foreach ($inventory->getItems() as $inventoryItem) {
                if ($inventoryItem->getId()->equals($item->getId())) {
                    $inventoryItem->decrementQuantity();
                }
            }

            $alteredItem = $itemRepo->createForInventory("step-ladder");
            $alteredItem->moveTo($item->getLocationId());
            $alteredItem->incrementQuantity();
            $inventory->add($alteredItem);

            /** @var Item $inventoryItem */
            foreach ($inventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }

            session()->flash("success", "You closed the Step Ladder.");
            return redirect("/{$gameId}");
        }

        if (!$item->hasUse()) {
            session()->flash("info", "That did nothing.");
            return redirect("/{$gameId}");
        }

        $use = $item->getUse();

        $player->gainXp($use->getXp());

        $this->playerRepo->save($player);

        session()->flash("success", "{$use->getMessage()} You gained {$use->getXp()} XP.");
        return redirect("/{$gameId}");
    }
}
