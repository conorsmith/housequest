<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Location;
use App\Repositories\LocationRepositoryConfig;
use stdClass;

final class LocationFactory
{
    /** @var array */
    private $config;

    /** @var LocationRepositoryConfig */
    private $locationRepo;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        array $config,
        LocationRepositoryConfig $locationRepo,
        ItemFactory $itemViewModelFactory
    ) {
        $this->config = $config;
        $this->locationRepo = $locationRepo;
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function create(Location $location): stdClass
    {
        $locationConfig = $this->config[$location->getId()];

        return (object) [
            'id'    => $location->getId(),
            'title' => $locationConfig['title'],
        ];
    }

    public function createPlayerLocation(Location $location, Inventory $inventory, array $locationItemSurfaceInventories): stdClass
    {
        $viewModel = $this->create($location);
        $viewModel->egresses = [];
        $viewModel->items = [];

        foreach ($location->getEgresses($inventory) as $egressLocationId) {
            $viewModel->egresses[] = $this->create(
                $this->locationRepo->find($egressLocationId)
            );
        }

        /** @var Item $item */
        foreach ($inventory->getItems() as $item) {
            $itemViewModel = $this->itemViewModelFactory->create($item);
            $itemViewModel->surface = $this->createSurface($item, $locationItemSurfaceInventories);
            $viewModel->items[] = $itemViewModel;
        }

        return $viewModel;
    }

    private function createSurface(Item $item, array $locationItemSurfaceInventories): array
    {
        $surfaceItems = [];

        /** @var Inventory $surfaceInventory */
        foreach ($locationItemSurfaceInventories as $surfaceInventory) {
            if ($surfaceInventory->isForItem($item)) {
                /** @var Item $item */
                foreach ($surfaceInventory->getItems() as $item) {
                    $surfaceItems[] = $this->itemViewModelFactory->create($item);
                }
            }
        }

        return $surfaceItems;
    }
}
