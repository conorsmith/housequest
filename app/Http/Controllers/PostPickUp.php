<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));
        $itemIds = [];

        foreach ($request->input("items") as $itemIdAsString) {
            $itemIds[] = Uuid::fromString($itemIdAsString);
        }

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create($gameId);

        $playerInventory = $itemRepo->findInventory(ItemWhereabouts::player());

        $failures = [];

        /** @var UuidInterface $itemId */
        foreach ($itemIds as $itemId) {
            $failure = $this->pickUp($itemRepo, $playerInventory, $itemId);

            if (!is_null($failure)) {
                $failures[] = $failure;
            }
        }

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        if (count($failures) > 0) {
            session()->flash("info[]", $failures);
        }

        return redirect("/{$gameId}");
    }

    private function pickUp(ItemRepository $itemRepo, Inventory $playerInventory, UuidInterface $itemId): ?string
    {
        $item = $itemRepo->find($itemId);
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getWhereabouts()->isPlayer()) {
            return "You cannot pick up {$viewModel->label}, you're already holding it.";
        }

        if ($item->isDangerous()) {
            return "You cannot pick up {$viewModel->label}, it's too dangerous to do so.";
        }

        if ($item->isAffixed()) {
            return "You cannot pick up {$viewModel->label}, it's fixed in place.";
        }

        if ($item->isHeavy()) {
            return "You cannot pick up {$viewModel->label}, it's too heavy.";
        }

        $playerInventory->add($item);

        return null;
    }
}
