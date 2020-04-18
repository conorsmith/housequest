<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Item;
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

        return (object) [
            'id'                          => $item->getId(),
            'typeId'                      => $item->getTypeId(),
            'label'                       => $itemConfig['name'],
            'quantity'                    => $item->getQuantity(),
            'hasAllPortions'              => $item->hasAllPortions(),
            'remainingPortions'           => $item->getRemainingPortions(),
            'totalPortions'               => $item->getTotalPortions(),
            'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            'isMultiPortionItem'          => $item->isMultiPortionItem(),
            'isContainer'                 => $item->isContainer(),
        ];
    }
}
