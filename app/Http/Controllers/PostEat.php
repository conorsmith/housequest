<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Domain\Player;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\AchievementFactory;
use App\ViewModels\EventFactory;
use App\ViewModels\ItemFactory;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

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

    public function __invoke(string $gameId, string $itemId)
    {
        $gameId = Uuid::fromString($gameId);
        $itemId = Uuid::fromString($itemId);
        $itemRepo = $this->itemRepoFactory->create($gameId);

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
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
            session()->flash("info", "You fail to eat {$viewModel->label}.");
            return redirect("/{$gameId}");
        }

        $inventory->removeEatenItem($itemId);

        $player->eat($item);

        $achievementIds = $this->unlockAchievements($player);

        if ($item->hasAttribute("toxic")) {
            $player->kill();
            $event = $player->experienceEvent("alien-grub");
            $eventViewModel = $this->eventViewModelFactory->create($event);
            session()->flash("messageRaw", $eventViewModel->message);
        }

        $this->playerRepo->save($player);
        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        $achievementSessionData = [];

        foreach ($achievementIds as $achievementId) {
            $achievementSessionData[] = $this->achievementViewModelFactory->create($achievementId);
        }

        if (count($achievementSessionData) > 0) {
            session()->flash("achievements", $achievementSessionData);
        }

        if (!isset($event)) {
            session()->flash("success", "You ate {$viewModel->label}.");
        }
        return redirect("/{$gameId}");
    }

    private function unlockAchievements(Player $player): array
    {
        $existingAchievementIds = $player->getAchievements();

        if ($player->getEatenItemsCount() === 5) {
            $player->unlockAchievement("eat_count_5");
        } elseif ($player->getEatenItemsCount() === 10) {
            $player->unlockAchievement("eat_count_10");
        } elseif ($player->getEatenItemsCount() === 25) {
            $player->unlockAchievement("eat_count_25");
        } elseif ($player->getEatenItemsCount() === 50) {
            $player->unlockAchievement("eat_count_50");
        }

        if (count($player->getEatenItemTypes()) === 5) {
            $player->unlockAchievement("eat_types_5");
        } elseif (count($player->getEatenItemTypes()) === 10) {
            $player->unlockAchievement("eat_types_10");
        } elseif (count($player->getEatenItemTypes()) === 25) {
            $player->unlockAchievement("eat_types_25");
        } elseif (count($player->getEatenItemTypes()) === 50) {
            $player->unlockAchievement("eat_types_50");
        } elseif (count($player->getEatenItemTypes()) === 57) {
            $player->unlockAchievement("eat_types_57");
        }

        $unlockedAchievementIds = [];

        foreach ($player->getAchievements() as $achievementId) {
            if (!in_array($achievementId, $existingAchievementIds)) {
                $unlockedAchievementIds[] = $achievementId;
            }
        }

        return $unlockedAchievementIds;
    }
}
