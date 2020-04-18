<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepository;
use Illuminate\Support\Arr;
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

            $state = Arr::get($itemConfig, "states.{$item->getState()}", "");
            if (is_array($state)) {
                $state = $state['label'];
            }

            $viewModel->contents[] = (object) [
                'id'                          => $item->getId(),
                'typeId'                      => $item->getTypeId(),
                'label'                       => $itemConfig['name'],
                'quantity'                    => $item->getQuantity(),
                'hasAllPortions'              => $item->hasAllPortions(),
                'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
                'state'                       => $state,
            ];
        }

        return $viewModel;
    }
}
