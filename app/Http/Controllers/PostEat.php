<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\AchievementFactory;
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

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepository $playerRepo,
        AchievementFactory $achievementViewModelFactory
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
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

        $inventory = $itemRepo->findInventory("player");

        $item = $inventory->find($itemId);

        if (is_null($item)) {
            $inventory = $itemRepo->findInventory($player->getLocationId());
            $item = $inventory->find($itemId);

            if (is_null($item)) {
                throw new InvalidArgumentException("Item '{$itemId}' not found in inventory.");
            }
        }

        if (!$item->isEdible()) {
            session()->flash("info", "You fail to eat {$item->getName()}.");
            return redirect("/{$gameId}");
        }

        $inventory->removeEatenItem($itemId);

        $player->eat($item);

        $achievementIds = $this->unlockAchievements($player);

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

        session()->flash("success", "You ate {$item->getName()}.");
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
