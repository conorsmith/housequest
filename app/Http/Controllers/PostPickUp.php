<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Ramsey\Uuid\Uuid;

final class PostPickUp extends Controller
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

    public function __invoke(string $gameId, string $itemId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $playerInventory = $itemRepo->findInventory("player");

        $item = $itemRepo->find(Uuid::fromString($itemId));
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getLocationId() === "player") {
            session()->flash("info", "You cannot pick up {$viewModel->label}, you're already holding it.");
            return redirect("/{$gameId}");
        }

        if ($item->isDangerous()) {
            session()->flash("info", "You cannot pick up {$viewModel->label}, it's too dangerous to do so.");
            return redirect("/{$gameId}");
        }

        if ($item->isAffixed()) {
            session()->flash("info", "You cannot pick up {$viewModel->label}, it's fixed in place.");
            return redirect("/{$gameId}");
        }

        if ($item->isHeavy()) {
            session()->flash("info", "You cannot pick up {$viewModel->label}, it's too heavy.");
            return redirect("/{$gameId}");
        }

        $playerInventory->add($item);

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        return redirect("/{$gameId}");
    }
}
