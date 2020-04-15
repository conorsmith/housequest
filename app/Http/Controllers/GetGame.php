<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\LocationRepositoryConfig;
use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class GetGame extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDbFactory */
    private $itemRepoFactory;

    public function __construct(PlayerRepository $playerRepo, ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(string $gameId)
    {
        $locations = include __DIR__ . "/../../../config/locations.php";

        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $locationRepo = new LocationRepositoryConfig($locations);

        $player = $this->playerRepo->find(Uuid::fromString($gameId));
        $activeLocationItems = $itemRepo->findAtLocation($player->getLocationId());
        $inventory = $itemRepo->getInventory();

        $locationId = $player->getLocationId();

        $activeLocationConfig = $locationRepo->findForPlayer($player);

        if ($locationId === "landing") {
            /** @var Item $item */
            foreach ($activeLocationItems as $item) {
                if ($item->getTypeId() === "deployed-step-ladder") {
                    $activeLocationConfig["egresses"][] = "attic";
                }
            }
        }

        $locationViewModel = (object) $activeLocationConfig;
        $locationViewModel->id = $locationId;
        $locationViewModel->egresses = [];
        $locationViewModel->objects = [];

        foreach ($activeLocationConfig['egresses'] as $egressLocationId) {
            $egressLocation = $locations[$egressLocationId];

            $locationViewModel->egresses[] = (object) [
                'id' => $egressLocationId,
                'label' => $egressLocation['title'],
            ];
        }

        /** @var Item $item */
        foreach ($activeLocationItems as $item) {
            $locationViewModel->objects[] = (object) [
                'id'                          => $item->getId(),
                'typeId'                      => $item->getTypeId(),
                'label'                       => $item->getName(),
                'quantity'                    => $item->getQuantity(),
                'hasAllPortions'              => $item->hasAllPortions(),
                'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
                'isContainer'                 => $item->isContainer(),
                'isUsable'                    => $item->hasUse()
                    && $item->getUse()->fromRoom()
            ];
        }

        $playerViewModel = (object) [
            'xp' => $player->getXp(),
            'isDead' => $player->isDead(),
            'inventory' => [],
        ];

        foreach ($inventory as $item) {
            $playerViewModel->inventory[] = (object) [
                'id'                          => $item->getId(),
                'typeId'                      => $item->getTypeId(),
                'label'                       => $item->getName(),
                'quantity'                    => $item->getQuantity(),
                'hasAllPortions'              => $item->hasAllPortions(),
                'remainingPortions'           => $item->getRemainingPortions(),
                'totalPortions'               => $item->getTotalPortions(),
                'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
                'isMultiPortionItem'          => $item->isMultiPortionItem(),
                'isEdible'                    => $item->isEdible(),
                'isContainer'                 => $item->isContainer(),
                'isUsable'                    => $item->hasUse()
                    && $item->getUse()->fromInventory(),
            ];
        }

        $containerViewModels = [];

        /** @var Item $item */
        foreach ($activeLocationItems as $item) {
            if ($item->isContainer()) {
                $containerViewModel = (object) [
                    'id'       => $item->getId(),
                    'typeId'   => $item->getTypeId(),
                    'label'    => $item->getName(),
                    'contents' => [],
                ];

                $containedItems = $itemRepo->findAllInContainer($item);

                /** @var Item $containedItem */
                foreach ($containedItems as $containedItem) {
                    $containerViewModel->contents[] = (object) [
                        'id'                          => $containedItem->getId(),
                        'typeId'                      => $containedItem->getTypeId(),
                        'label'                       => $containedItem->getName(),
                        'quantity'                    => $containedItem->getQuantity(),
                        'hasAllPortions'              => $containedItem->hasAllPortions(),
                        'remainingPortionsPercentage' => $containedItem->getRemainingPortions() / $containedItem->getTotalPortions() * 100,
                    ];
                }

                $containerViewModels[] = $containerViewModel;
            }
        }

        /** @var Item $item */
        foreach ($inventory as $item) {
            if ($item->isContainer()) {
                $containerViewModel = (object) [
                    'id'       => $item->getId(),
                    'typeId'   => $item->getTypeId(),
                    'label'    => $item->getName(),
                    'contents' => [],
                ];

                $containedItems = $itemRepo->findAllInContainer($item);

                /** @var Item $containedItem */
                foreach ($containedItems as $containedItem) {
                    $containerViewModel->contents[] = (object) [
                        'id'                          => $containedItem->getId(),
                        'typeId'                      => $containedItem->getTypeId(),
                        'label'                       => $containedItem->getName(),
                        'quantity'                    => $containedItem->getQuantity(),
                        'hasAllPortions'              => $containedItem->hasAllPortions(),
                        'remainingPortionsPercentage' => $containedItem->getRemainingPortions() / $containedItem->getTotalPortions() * 100,
                    ];
                }

                $containerViewModels[] = $containerViewModel;
            }
        }

        return view('home', [
            'gameId' => $gameId,
            'location' => $locationViewModel,
            'player' => $playerViewModel,
            'containers' => $containerViewModels,
        ]);
    }
}
