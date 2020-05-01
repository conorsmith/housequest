<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\AchievementFactory;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PostUseWith extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    /** @var AchievementFactory */
    private $achievementViewModelFactory;

    public function __construct(
        PlayerRepository $playerRepo,
        ItemRepositoryDbFactory $itemRepoFactory,
        ItemFactory $itemViewModelFactory,
        AchievementFactory $achievementViewModelFactory
    ) {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
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

        $items = [];

        /** @var UuidInterface $itemId */
        foreach ($itemIds as $itemId) {
            $items[] = $itemRepo->find($itemId);
        }

        $combo = $this->findCombo($items);

        if (is_null($combo)) {
            session()->flash("info", "That did nothing");
            return redirect("/{$gameId}");
        }

        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->isExhaustible()) {
                $inventory = $itemRepo->findInventory($item->getWhereabouts());

                $inventory->removeExpendedItem($item->getId());

                /** @var Item $inventoryItem */
                foreach ($inventory->getItems() as $inventoryItem) {
                    $itemRepo->save($inventoryItem);
                }
            }

            if ($item->getTypeId() === "face-cloth") {
                $item->transitionState("wet");
                $itemRepo->save($item);
            }
        }

        if (array_key_exists('resultingConditions', $combo)) {
            /** @var string $condition */
            foreach ($combo['resultingConditions'] as $condition) {
                $player->addCondition($condition);
            }
        }

        $player->useCombo($items);

        $achievementIds = $player->unlockAchievements();

        $this->playerRepo->save($player);

        $achievementSessionData = [];

        foreach ($achievementIds as $achievementId) {
            $achievementSessionData[] = $this->achievementViewModelFactory->create($achievementId);
        }

        if (count($achievementSessionData) > 0) {
            session()->flash("achievements", $achievementSessionData);
        }

        session()->flash("success", $combo['message']);
        return redirect("/{$gameId}");
    }

    private function findCombo(array $items): ?array
    {
        $combos = [
            [
                'items' => ["toothbrush", "toothpaste"],
                'message' => "You brush your teeth without water, leaving you with a pasty mouth.",
            ],
            [
                'items' => ["toothbrush", "toothpaste", "sink"],
                'message' => "You brush your teeth well.",
            ],
            [
                'items' => ["toothbrush", "toothpaste", "shower"],
                'message' => "You brush your teeth in the shower, for some reason.",
            ],
            [
                'items' => ["toothbrush", "toothpaste", "toilet"],
                'message' => "You brush your teeth using the worst water source you could have chosen.",
            ],
            [
                'items' => ["sink", "soap"],
                'message' => "You wash your hands with soap for the recommended time. Great work!",
                'resultingConditions' => ["wet-hands"],
            ],
            [
                'items' => ["disposable-razor", "shaving-cream"],
                'message' => "You give yourself an alright shave but a bit of water would have made things smoother.",
            ],
            [
                'items' => ["disposable-razor", "shaving-cream", "sink"],
                'message' => "You give yourself a nice smooth shave.",
                'resultingConditions' => ["wet-body"],
            ],
            [
                'items' => ["disposable-razor", "shaving-cream", "shower"],
                'message' => "You give yourself a nice smooth shave in the shower.",
                'resultingConditions' => ["wet-body"],
            ],
            [
                'items' => ["disposable-razor", "shaving-cream", "shower"],
                'message' => "You give yourself a smooth shave using toilet water. Why??",
                'resultingConditions' => ["wet-body"],
            ],
            [
                'items' => ["face-cloth", "sink"],
                'message' => "You give your face a nice rinse.",
                'resultingConditions' => ["wet-face"],
            ],
            [
                'items' => ["face-cloth", "shower"],
                'message' => "You give your face a nice rinse in the shower, for some reason.",
                'resultingConditions' => ["wet-face"],
            ],
            [
                'items' => ["face-cloth", "toilet"],
                'message' => "You make your face less clean by using toilet water, you utter fool.",
                'resultingConditions' => ["wet-face"],
            ],
            [
                'items' => ["face-cloth", "soap", "sink"],
                'message' => "You give your face a nice clean.",
                'resultingConditions' => ["wet-face"],
            ],
            [
                'items' => ["face-cloth", "soap", "shower"],
                'message' => "You give your face a nice clean in the shower, for some reason.",
                'resultingConditions' => ["wet-face"],
            ],
            [
                'items' => ["face-cloth", "soap", "toilet"],
                'message' => "You make your face less clean by using toilet water, you utter fool.",
                'resultingConditions' => ["wet-face"],
            ],
        ];

        $itemTypeIds = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $itemTypeIds[] = $item->getTypeId();
        }

        sort($itemTypeIds);

        foreach ($combos as $combo) {
            sort($combo['items']);
            if ($combo['items'] === $itemTypeIds) {
                return $combo;
            }
        }

        return null;
    }

    private function isCombo(array $combo, array $items): bool
    {
        return !array_diff($combo['items'], $items)
            && !array_diff($items, $combo['items']);
    }
}
