<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Ramsey\Uuid\Uuid;

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

    public function __invoke(string $gameId, string $itemId, string $locationId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $itemId  = Uuid::fromString($itemId);

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $playerInventory = $itemRepo->findInventory(ItemWhereabouts::player());
        $locationInventory = $itemRepo->findInventory(ItemWhereabouts::location($locationId));

        if (is_null($playerInventory->find($itemId))) {
            $item = $locationInventory->find($itemId);
            $viewModel = $this->itemViewModelFactory->create($item);
            session()->flash("info", "You cannot drop {$viewModel->label}, you're not holding it.");
            return redirect("/{$gameId}");
        }

        $item = $playerInventory->remove($itemId);
        $locationInventory->add($item);

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $itemRepo->save($item);
        }
        /** @var Item $item */
        foreach ($locationInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        return redirect("/{$gameId}");
    }
}
