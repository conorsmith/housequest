<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostLookAt extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDbFactory */
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
        $itemId = Uuid::fromString($request->route("itemId"));
        $itemRepo = $this->itemRepoFactory->create($gameId);

        $player = $this->playerRepo->find($gameId);

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        if ($itemId->toString() === "00000000-0000-0000-0000-000000000000") {
            $playerMessage = "It's {$player->getName()}.";

            foreach ($player->getConditions() as $condition) {
                if ($condition === "wet-body") {
                    $playerMessage .= " You are wet.";
                } elseif ($condition === "wet-hands") {
                    $playerMessage .= " Your hands are wet.";
                } elseif ($condition === "wet-face") {
                    $playerMessage .= " Your face is wet.";
                }
            }

            session()->flash("messageRaw", $playerMessage);
            return redirect("/{$gameId}");
        }

        $item = $itemRepo->find($itemId);
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->isMultiPortionItem()
            && $item->getRemainingPortions() < $item->getTotalPortions()
        ) {
            $portionMessage = " [{$item->getRemainingPortions()}/{$item->getTotalPortions()}]";
        } else {
            $portionMessage = "";
        }

        if ($viewModel->hasDescription) {
            $description = str_replace("{player}", $player->getName(), $viewModel->description);
            session()->flash("messageRaw", $description . $portionMessage);
        } else {
            session()->flash("messageRaw", "It's {$viewModel->label}.{$portionMessage}");
        }
        return redirect("/{$gameId}");
    }
}
