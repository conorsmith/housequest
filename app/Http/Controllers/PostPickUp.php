<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class PostPickUp extends Controller
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

    public function __invoke(string $gameId, string $itemId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $playerInventory = $itemRepo->findInventory("player");

        $item = $itemRepo->find($itemId);

        if ($item->getLocationId() === "player") {
            session()->flash("info", "You cannot pick up {$item->getName()}, you're already holding it.");
            return redirect("/{$gameId}");
        }

        if ($item->isDangerous()) {
            session()->flash("info", "You cannot pick up {$item->getName()}, it's too dangerous to do so.");
            return redirect("/{$gameId}");
        }

        if ($item->isAffixed()) {
            session()->flash("info", "You cannot pick up {$item->getName()}, it's fixed in place.");
            return redirect("/{$gameId}");
        }

        if ($item->isHeavy()) {
            session()->flash("info", "You cannot pick up {$item->getName()}, it's too heavy.");
            return redirect("/{$gameId}");
        }

        $playerInventory->add($item);

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        return redirect("/{$gameId}");
    }
}
