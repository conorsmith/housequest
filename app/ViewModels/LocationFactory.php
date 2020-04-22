<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\InventoryTreeNode;
use App\Domain\Location;
use App\Repositories\LocationRepositoryConfig;
use stdClass;

final class LocationFactory
{
    /** @var array */
    private $config;

    /** @var LocationRepositoryConfig */
    private $locationRepo;

    /** @var InventoryFactory */
    private $inventoryViewModelFactory;

    public function __construct(
        array $config,
        LocationRepositoryConfig $locationRepo,
        InventoryFactory $inventoryViewModelFactory
    ) {
        $this->config = $config;
        $this->locationRepo = $locationRepo;
        $this->inventoryViewModelFactory = $inventoryViewModelFactory;
    }

    public function create(Location $location): stdClass
    {
        $locationConfig = $this->config[$location->getId()];

        return (object) [
            'id'    => $location->getId(),
            'title' => $locationConfig['title'],
        ];
    }

    public function createPlayerLocation(Location $location, InventoryTreeNode $inventoryTree): stdClass
    {
        $viewModel = $this->create($location);
        $viewModel->egresses = [];
        $viewModel->items = $this->inventoryViewModelFactory->fromInventoryTree($inventoryTree);

        foreach ($location->getEgresses($inventoryTree->getInventory()) as $egressLocationId) {
            $viewModel->egresses[] = $this->create(
                $this->locationRepo->find($egressLocationId)
            );
        }

        return $viewModel;
    }
}
