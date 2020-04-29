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
            $stateLabel = $state['label'];
        } else {
            $stateLabel = $state;
        }

        if ($item->isContainer() && $item->getState() === "open") {
            $stateLabel = "Open";
        }

        $description = Arr::get($itemConfig, 'description', "");

        if (is_array($state) && array_key_exists('description', $state)) {
            $description = $state['description'];
        }

        return (object) [
            'id'                          => $item->getId(),
            'typeId'                      => $item->getTypeId(),
            'label'                       => $itemConfig['name'],
            'hasDescription'              => $description !== "",
            'description'                 => $description,
            'quantity'                    => $item->getQuantity(),
            'hasAllPortions'              => $item->hasAllPortions(),
            'remainingPortions'           => $item->getRemainingPortions(),
            'totalPortions'               => $item->getTotalPortions(),
            'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            'state'                       => $stateLabel,
            'isMultiPortionItem'          => $item->isMultiPortionItem(),
            'isContainer'                 => $item->isContainer(),
        ];
    }

    public function createUseMessages(Item $item): stdClass
    {
        $itemConfig = $this->config[$item->getTypeId()];
        $states = Arr::get($itemConfig, "states", []);
        $stateMessages = [];

        foreach ($states as $stateId => $stateConfig) {
            if (is_array($stateConfig)
                && array_key_exists('use', $stateConfig)
            ) {
                $stateMessages[$stateId] = $stateConfig['use'];
            }
        }


        return (object) [
            'states' => $stateMessages,
        ];
    }
}
