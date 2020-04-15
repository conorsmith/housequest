<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class PostDrop extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    public function __construct(PlayerRepository $playerRepo, ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(string $gameId, string $itemId, string $locationId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $itemId  = Uuid::fromString($itemId);

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $playerInventory = new Inventory("player", $itemRepo->getInventory());
        $locationInventory = new Inventory($locationId, $itemRepo->findAtLocation($locationId));

        if (is_null($playerInventory->find($itemId))) {
            $item = $locationInventory->find($itemId);
            session()->flash("info", "You cannot drop {$item->getName()}, you're not holding it.");
            return redirect("/{$gameId}");
        }

        $item = $playerInventory->remove($itemId);
        $locationInventory->add($item);

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $itemRepo->save($item);
        }
        /** @var Item $item */
        foreach ($locationInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        return redirect("/{$gameId}");
    }
}
