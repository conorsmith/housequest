<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use Ramsey\Uuid\Uuid;

final class PostUse extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory, PlayerRepositoryDb $playerRepo)
    {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
    }

    public function __invoke(string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));
        $item = $itemRepo->find($itemId);

        if (!$item->hasUse()) {
            session()->flash("info", "That did nothing.");
            return redirect("/{$gameId}");
        }

        $use = $item->getUse();

        $player->gainXp($use->getXp());

        $this->playerRepo->save($player);

        session()->flash("success", "{$use->getMessage()} You gained {$use->getXp()} XP.");
        return redirect("/{$gameId}");
    }
}
