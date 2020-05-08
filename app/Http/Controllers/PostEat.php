<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\AchievementFactory;
use App\ViewModels\EventFactory;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;

final class PostEat extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepository */
    private $playerRepo;

    /** @var AchievementFactory */
    private $achievementViewModelFactory;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    /** @var EventFactory */
    private $eventViewModelFactory;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepository $playerRepo,
        AchievementFactory $achievementViewModelFactory,
        ItemFactory $itemViewModelFactory,
        EventFactory $eventViewModelFactory
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
        $this->eventViewModelFactory = $eventViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));

        $itemIds = [];

        if (is_null($request->input("items"))) {
            $itemIds[] = Uuid::fromString($request->route("itemId"));
        } else {
            foreach ($request->input("items") as $itemIdAsString) {
                $itemIds[] = Uuid::fromString($itemIdAsString);
            }
        }

        $itemRepo = $this->itemRepoFactory->create($gameId);

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $items = [];
        $achievementIds = [];
        $events = [];
        $failures = [];

        /** @var UuidInterface $itemId */
        foreach ($itemIds as $itemId) {

            $result = $this->eat($itemRepo, $player, $itemId);

            if ($result->isSuccessful()) {
                $items[] = $result->getItem();
                $achievementIds = array_merge($achievementIds, $result->getAchievementIds());

            } elseif ($result->isEvent()) {
                $events[] = $result->getEvent();

            } else {
                $failures[] = $result->getFailureMessage();
            }
        }

        $this->playerRepo->save($player);

        $achievementSessionData = [];

        foreach ($achievementIds as $achievementId) {
            $achievementSessionData[] = $this->achievementViewModelFactory->create($achievementId);
        }

        if (count($achievementSessionData) > 0) {
            session()->flash("achievements", $achievementSessionData);
        }

        if (count($items) > 0) {
            $messages = [];

            foreach ($items as $itemViewModel) {
                $messages[] = "You ate {$itemViewModel->label}.";
            }

            session()->flash("success[]", $messages);
        }

        if (count($events) > 0) {
            foreach ($events as $eventViewModel) {
                session()->flash("messageRaw", $eventViewModel->message);
            }
        }

        if (count($failures) > 0) {
            session()->flash("info[]", $failures);
        }

        return redirect("/{$gameId}");
    }

    private function eat(ItemRepository $itemRepo, Player $player, UuidInterface $itemId)
    {
        if ($itemId->toString() === "00000000-0000-0000-0000-000000000000") {
            return $this->createFailedResult("You cannot eat yourself.");
        }

        $item = $itemRepo->find($itemId);
        $rootWhereabouts = $itemRepo->findRootWhereabouts($item);

        if (!$rootWhereabouts->isPlayer()
            && !$rootWhereabouts->isLocation($player->getLocationId())
        ) {
            throw new InvalidArgumentException("Item '{$itemId}' not available in current location.");
        }

        $inventory = $itemRepo->findInventory($item->getWhereabouts());

        $viewModel = $this->itemViewModelFactory->create($item);

        if (!$item->isIngestible()) {
            return $this->createFailedResult("You fail to eat {$viewModel->label}.");
        }

        $inventory->removeEatenItem($itemId);

        $player->eat($item);

        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        if ($item->hasAttribute("toxic")) {
            $player->kill();
            $event = $player->experienceEvent("alien-grub");
            $eventViewModel = $this->eventViewModelFactory->create($event);
            return $this->createEventResult($eventViewModel);
        }

        return $this->createSuccessfulResult(
            $viewModel,
            $player->unlockAchievements()
        );
    }

    private function createSuccessfulResult($itemViewModel, array $achievementIds)
    {
        return $this->createResult(true, null, $itemViewModel, $achievementIds, null);
    }

    private function createFailedResult(string $failureMessage)
    {
        return $this->createResult(false, $failureMessage, null, null, null);
    }

    private function createEventResult($eventViewModel)
    {
        return $this->createResult(false, null, null, null, $eventViewModel);
    }

    private function createResult(bool $success, ?string $failureMessage, ?stdClass $itemViewModel, ?array $achievementIds, ?stdClass $eventViewModel)
    {
        return new class($success, $failureMessage, $itemViewModel, $achievementIds, $eventViewModel)
        {
            /** @var bool */
            private $success;

            /** @var ?string */
            private $failureMessage;

            /** @var ?stdClass */
            private $itemViewModel;

            /** @var ?array */
            private $achievementIds;

            /** @var ?stdClass */
            private $eventViewModel;

            public function __construct(bool $success, ?string $failureMessage, ?stdClass $itemViewModel, ?array $achievementIds, ?stdClass $eventViewModel)
            {
                $this->success = $success;
                $this->failureMessage = $failureMessage;
                $this->itemViewModel = $itemViewModel;
                $this->achievementIds = $achievementIds;
                $this->eventViewModel = $eventViewModel;
            }

            public function isSuccessful(): bool
            {
                return $this->success;
            }

            public function isEvent(): bool
            {
                return !is_null($this->eventViewModel);
            }

            public function getFailureMessage(): string
            {
                return $this->failureMessage;
            }

            public function getItem(): stdClass
            {
                return $this->itemViewModel;
            }

            public function getAchievementIds(): array
            {
                return $this->achievementIds;
            }

            public function getEvent(): stdClass
            {
                return $this->eventViewModel;
            }
        };
    }
}
