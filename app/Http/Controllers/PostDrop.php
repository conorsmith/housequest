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

        $modifiedInventories = [];
        $failures = [];
        $locationInventory = $itemRepo->findInventory(ItemWhereabouts::location($locationId));

        /** @var UuidInterface $itemId */
        foreach ($itemIds as $itemId) {
            $result = $this->drop($itemRepo, $locationInventory, $itemId);

            if ($result->isSuccessful()) {
                $modifiedInventories[] = $result->getModifiedInventory();
            } else {
                $failures[] = $result->getFailureMessage();
            }
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

        if (count($failures) > 0) {
            session()->flash("info[]", $failures);
        }

        return redirect("/{$gameId}");
    }

    private function drop(ItemRepository $itemRepo, Inventory $locationInventory, UuidInterface $itemId)
    {
        $item = $itemRepo->find($itemId);
        $rootWhereabouts = $itemRepo->findRootWhereabouts($item);

        if (!$rootWhereabouts->isPlayer()) {
            $viewModel = $this->itemViewModelFactory->create($item);
            return $this->createdFailedResult("You cannot drop {$viewModel->label}, you're not holding it.");
        }

        $inventory = $itemRepo->findInventory($item->getWhereabouts());
        $item = $inventory->remove($itemId);
        $locationInventory->add($item);

        return $this->createSuccessfulResult($inventory);
    }

    private function createSuccessfulResult(Inventory $modifiedInventory)
    {
        return $this->createResult(true, null, $modifiedInventory);
    }

    private function createdFailedResult(string $failureMessage)
    {
        return $this->createResult(false, $failureMessage, null);
    }

    private function createResult(bool $success, ?string $failureMessage, ?Inventory $modifiedInventory)
    {
        return new class($success, $failureMessage, $modifiedInventory)
        {
            /** @var bool */
            private $success;

            /** @var ?string */
            private $failureMessage;

            /** @var ?Inventory */
            private $modifiedInventory;

            public function __construct(bool $success, ?string $failureMessage, ?Inventory $modifiedInventory)
            {
                $this->success = $success;
                $this->failureMessage = $failureMessage;
                $this->modifiedInventory = $modifiedInventory;
            }

            public function isSuccessful(): bool
            {
                return $this->success;
            }

            public function getFailureMessage(): string
            {
                return $this->failureMessage;
            }

            public function getModifiedInventory(): Inventory
            {
                return $this->modifiedInventory;
            }
        };
    }
}
