<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Item;
use Illuminate\Support\Arr;
use stdClass;

final class ItemFactory
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(Item $item): stdClass
    {
        $itemConfig = $this->config[$item->getTypeId()];

        $state = Arr::get($itemConfig, "states.{$item->getState()}", "");
        if (is_array($state)) {
            $state = $state['label'];
        }

        return (object) [
            'id'                          => $item->getId(),
            'typeId'                      => $item->getTypeId(),
            'label'                       => $itemConfig['name'],
            'quantity'                    => $item->getQuantity(),
            'hasAllPortions'              => $item->hasAllPortions(),
            'remainingPortions'           => $item->getRemainingPortions(),
            'totalPortions'               => $item->getTotalPortions(),
            'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            'state'                       => $state,
            'isMultiPortionItem'          => $item->isMultiPortionItem(),
            'isContainer'                 => $item->isContainer(),
        ];
    }
}
