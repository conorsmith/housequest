<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PostDrop extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        PlayerRepository $playerRepo,
        ItemRepositoryDbFactory $itemRepoFactory,
        ItemFactory $itemViewModelFactory
    ) {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));
        $locationId = $request->route("locationId");

        if (is_null($request->route("itemId"))) {
            $itemIds = [];
            foreach ($request->input("items") as $itemIdAsString) {
                $itemIds[] = Uuid::fromString($itemIdAsString);
            }
        } else {
            $itemIds = [
                Uuid::fromString($request->route("itemId"))
            ];
        }

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create($gameId);

        $modifiedInventories = [];
        $locationInventory = $itemRepo->findInventory(ItemWhereabouts::location($locationId));

        /** @var UuidInterface $itemId */
        foreach ($itemIds as $itemId) {

            $item = $itemRepo->find($itemId);
            $rootWhereabouts = $itemRepo->findRootWhereabouts($item);

            if (!$rootWhereabouts->isPlayer()) {
                $viewModel = $this->itemViewModelFactory->create($item);
                session()->flash("info", "You cannot drop {$viewModel->label}, you're not holding it.");
                return redirect("/{$gameId}");
            }

            $inventory = $itemRepo->findInventory($item->getWhereabouts());
            $item = $inventory->remove($itemId);
            $locationInventory->add($item);

            $modifiedInventories[] = $inventory;
        }

        /** @var Inventory $inventory */
        foreach ($modifiedInventories as $inventory) {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                $itemRepo->save($item);
            }
        }
        /** @var Item $item */
        foreach ($locationInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        return redirect("/{$gameId}");
    }
}
