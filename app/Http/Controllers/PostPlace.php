<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostPlace extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepositoryDb $playerRepo,
        ItemFactory $itemViewModelFactory
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));
        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $itemRepo = $this->itemRepoFactory->create($gameId);
        $subjectItem = $itemRepo->find(Uuid::fromString($request->input("itemSubjectId")));
        $targetItem = $itemRepo->find(Uuid::fromString($request->input("itemTargetId")));

        $subjectViewModel = $this->itemViewModelFactory->create($subjectItem);
        $targetViewModel = $this->itemViewModelFactory->create($targetItem);

        if ($subjectItem->isDangerous()) {
            session()->flash("info", "You cannot move {$subjectViewModel->label}, it's too dangerous to do so.");
            return redirect("/{$gameId}");
        }

        if ($subjectItem->isAffixed()) {
            session()->flash("info", "You cannot move {$subjectViewModel->label}, it's fixed in place.");
            return redirect("/{$gameId}");
        }

        if ($subjectItem->isHeavy()) {
            session()->flash("info", "You cannot move {$subjectViewModel->label}, it's too heavy.");
            return redirect("/{$gameId}");
        }

        $subjectItem->moveTo(ItemWhereabouts::itemSurface($targetItem->getId()->toString()));

        $itemRepo->save($subjectItem);

        session()->flash("success", "You placed {$subjectViewModel->label} on {$targetViewModel->label}.");
        return redirect("/{$gameId}");
    }
}
