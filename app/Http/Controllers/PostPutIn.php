<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

final class PostPutIn extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        PlayerRepository $playerRepo,
        ItemRepositoryDbFactory $itemRepoFactory,
        ItemFactory $itemViewModelFactory
    ) {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));
        $itemSubjectId = Uuid::fromString($request->input("itemSubjectId"));
        $itemTargetId = Uuid::fromString($request->input("itemTargetId"));

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        /** @var ItemRepositoryDb $itemRepo */
        $itemRepo = $this->itemRepoFactory->create($gameId);

        $subjectItem = $itemRepo->find($itemSubjectId);
        $targetItem = $itemRepo->find($itemTargetId);

        $subjectItemViewModel = $this->itemViewModelFactory->create($subjectItem);
        $targetItemViewModel = $this->itemViewModelFactory->create($targetItem);

        if (!$targetItem->isContainer()) {
            session()->flash("info", "You cannot put {$subjectItemViewModel->label} in {$targetItemViewModel->label}.");
            return redirect("/{$gameId}");
        }

        $subjectItem->moveTo(
            ItemWhereabouts::itemContents($targetItem->getId()->toString())
        );

        $itemRepo->save($subjectItem);

        session()->flash("success", "You put {$subjectItemViewModel->label} in {$targetItemViewModel->label}.");
        return redirect("/{$gameId}");
    }
}
