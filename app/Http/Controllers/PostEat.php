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
use Carbon\Carbon;
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
        $inventory = new Inventory("player", $itemRepo->getInventory());

        $item = $inventory->find($itemId);

        if (is_null($item)) {
            throw new InvalidArgumentException("Item '{$itemId}' not found in inventory.");
        }

        if (!$item->isEdible()) {
            session()->flash("info", "You fail to eat {$item->getName()}.");
            return redirect("/{$gameId}");
        }

        $xp = 10 * $item->getRemainingPortions();

        $inventory->removeEatenItem($itemId);

        $player->eat($item);
        $player->gainXp($xp);

        $achievements = $this->findAchievements($player);

        foreach ($achievements as $achievement) {
            \DB::table("achievements")
                ->insert([
                    'id'             => Uuid::uuid4(),
                    'player_id'      => $player->getId(),
                    'achievement_id' => $achievement['id'],
                    'created_at'     => Carbon::now("Europe/Dublin"),
                ]);
        }

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

        session()->flash("success", "You ate {$item->getName()}. You gained 10 XP.");
        return redirect("/{$gameId}");
    }

    private function findAchievements(Player $player): array
    {
        $rows = \DB::select("SELECT * FROM achievements WHERE player_id = ?", [
            $player->getId(),
        ]);

        $existingAchievements = [];

        foreach ($rows as $row) {
            $existingAchievements[] = $row->achievement_id;
        }

        $achievements = [];

        if ($player->getEatenItemsCount() === 5) {
            $achievements[] = $this->achievementRepo->find("eat_count_5");
        } elseif ($player->getEatenItemsCount() === 10) {
            $achievements[] = $this->achievementRepo->find("eat_count_10");
        } elseif ($player->getEatenItemsCount() === 25) {
            $achievements[] = $this->achievementRepo->find("eat_count_25");
        } elseif ($player->getEatenItemsCount() === 50) {
            $achievements[] = $this->achievementRepo->find("eat_count_50");
        }

        if (count($player->getEatenItemTypes()) === 5) {
            $achievements[] = $this->achievementRepo->find("eat_types_5");
        } elseif (count($player->getEatenItemTypes()) === 10) {
            $achievements[] = $this->achievementRepo->find("eat_types_10");
        } elseif (count($player->getEatenItemTypes()) === 25) {
            $achievements[] = $this->achievementRepo->find("eat_types_25");
        } elseif (count($player->getEatenItemTypes()) === 50) {
            $achievements[] = $this->achievementRepo->find("eat_types_50");
        } elseif (count($player->getEatenItemTypes()) === 57) {
            $achievements[] = $this->achievementRepo->find("eat_types_57");
        }

        foreach ($achievements as $key => $achievement) {
            if (in_array($achievement['id'], $existingAchievements)) {
                unset($achievements[$key]);
            }
        }

        return $achievements;
    }
}
