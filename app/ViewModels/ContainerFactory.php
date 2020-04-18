<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepository;
use stdClass;

final class ContainerFactory
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(Item $item, Inventory $contents): stdClass
    {
        $itemConfig = $this->config[$item->getTypeId()];

        $viewModel = (object) [
            'id'       => $item->getId(),
            'typeId'   => $item->getTypeId(),
            'label'    => $itemConfig['name'],
            'contents' => [],
        ];

        /** @var Item $item */
        foreach ($contents->getItems() as $item) {
            $itemConfig = $this->config[$item->getTypeId()];

            $viewModel->contents[] = (object) [
                'id'                          => $item->getId(),
                'typeId'                      => $item->getTypeId(),
                'label'                       => $itemConfig['name'],
                'quantity'                    => $item->getQuantity(),
                'hasAllPortions'              => $item->hasAllPortions(),
                'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            ];
        }

        return $viewModel;
    }
}
