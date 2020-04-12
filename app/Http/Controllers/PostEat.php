<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class PostEat extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepository */
    private $playerRepo;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory, PlayerRepository $playerRepo)
    {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
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

        $inventory->eat($itemId);

        $player->gainXp(10);

        $this->playerRepo->save($player);
        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        session()->flash("success", "You ate {$item->getName()}. You gained 10 XP.");
        return redirect("/{$gameId}");
    }
}
