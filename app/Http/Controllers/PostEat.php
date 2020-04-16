<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\AchievementRepositoryConfig;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class PostEat extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepository */
    private $playerRepo;

    /** @var AchievementRepositoryConfig */
    private $achievementRepo;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepository $playerRepo,
        AchievementRepositoryConfig $achievementRepo
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->achievementRepo = $achievementRepo;
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

        $inventory = new Inventory("player", $itemRepo->getInventory());

        $item = $inventory->find($itemId);

        if (is_null($item)) {
            $inventory = new Inventory($player->getLocationId(), $itemRepo->findAtLocation($player->getLocationId()));
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

        $achievements = $this->unlockAchievements($player);

        $this->playerRepo->save($player);
        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        $achievementSessionData = [];

        foreach ($achievements as $achievement) {
            $achievementSessionData[] = [
                'title' => $achievement['title'],
                'body'  => $achievement['body'],
            ];
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

        $unlockedAchievements = [];

        foreach ($player->getAchievements() as $achievementId) {
            if (!in_array($achievementId, $existingAchievementIds)) {
                $unlockedAchievements[] = $this->achievementRepo->find($achievementId);
            }
        }

        return $unlockedAchievements;
    }
}
