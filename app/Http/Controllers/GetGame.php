<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\LocationRepositoryConfig;
use App\Repositories\PlayerRepository;
use App\ViewModels\ContainerFactory;
use App\ViewModels\LocationFactory;
use App\ViewModels\PlayerFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class GetGame extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDbFactory */
    private $itemRepoFactory;

    /** @var LocationRepositoryConfig */
    private $locationRepo;

    /** @var LocationFactory */
    private $locationViewModelFactory;

    /** @var PlayerFactory */
    private $playerViewModelFactory;

    /** @var ContainerFactory */
    private $containerViewModelFactory;

    public function __construct(
        PlayerRepository $playerRepo,
        ItemRepositoryDbFactory $itemRepoFactory,
        LocationRepositoryConfig $locationRepo,
        LocationFactory $locationViewModelFactory,
        PlayerFactory $playerViewModelFactory,
        ContainerFactory $containerViewModelFactory
    ) {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
        $this->locationRepo = $locationRepo;
        $this->locationViewModelFactory = $locationViewModelFactory;
        $this->playerViewModelFactory = $playerViewModelFactory;
        $this->containerViewModelFactory = $containerViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::fromString($request->route("gameId"));

        $itemRepo = $this->itemRepoFactory->create($gameId);

        $player = $this->playerRepo->find($gameId);
        $location = $this->locationRepo->findForPlayer($player);

        $playerInventory = $itemRepo->findInventory(ItemWhereabouts::player());
        $locationInventory = $itemRepo->findInventory(ItemWhereabouts::location($player->getLocationId()));

        $locationItemSurfaceInventories = [];

        /** @var Item $item */
        foreach ($locationInventory->getItems() as $item) {
            $locationItemSurfaceInventories[] = $itemRepo->findInventory(ItemWhereabouts::itemSurface($item->getId()->toString()));
        }

        $locationViewModel = $this->locationViewModelFactory->createPlayerLocation($location, $locationInventory, $locationItemSurfaceInventories);

        $playerItemSurfaceInventories = [];

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            $playerItemSurfaceInventories[] = $itemRepo->findInventory(ItemWhereabouts::itemSurface($item->getId()->toString()));
        }

        $playerViewModel = $this->playerViewModelFactory->create($player, $playerInventory, $playerItemSurfaceInventories);

        $containerViewModels = [];

        /** @var Item $item */
        foreach ($locationInventory->getItems() as $item) {
            if ($item->isContainer()) {
                $containerViewModels[] = $this->containerViewModelFactory->create(
                    $item,
                    $itemRepo->findInventory(ItemWhereabouts::itemContents($item->getId()->toString()))
                );
            }
        }

        /** @var Item $item */
        foreach ($playerInventory->getItems() as $item) {
            if ($item->isContainer()) {
                $containerViewModels[] = $this->containerViewModelFactory->create(
                    $item,
                    $itemRepo->findInventory(ItemWhereabouts::itemContents($item->getId()->toString()))
                );
            }
        }

        return view('home', [
            'gameId'     => $gameId,
            'location'   => $locationViewModel,
            'player'     => $playerViewModel,
            'containers' => $containerViewModels,
        ]);
    }
}
