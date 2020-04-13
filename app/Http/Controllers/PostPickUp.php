<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use Ramsey\Uuid\Uuid;

final class PostPickUp extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(string $gameId, string $itemId)
    {
        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $playerInventory = new Inventory("player", $itemRepo->getInventory());

        $item = $itemRepo->find($itemId);

        if ($item->getLocationId() === "player") {
            session()->flash("info", "You cannot pick up {$item->getName()}, you're already holding it.");
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
