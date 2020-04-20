<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Event;
use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use stdClass;

final class PlayerFactory
{
    /** @var ItemFactory */
    private $itemViewModelFactory;

    /** @var EventFactory */
    private $eventViewModelFactory;

    /** @var AchievementFactory */
    private $achievementViewModelFactory;

    public function __construct(
        ItemFactory $itemViewModelFactory,
        EventFactory $eventViewModelFactory,
        AchievementFactory $achievementViewModelFactory
    ) {
        $this->itemViewModelFactory = $itemViewModelFactory;
        $this->eventViewModelFactory = $eventViewModelFactory;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
    }

    public function create(Player $player, Inventory $inventory, array $locationItemSurfaceInventories): stdClass
    {
        $viewModel = (object) [
            'name'         => $player->getName(),
            'isDead'       => $player->isDead(),
            'hasWon'       => $player->hasWon(),
            'inventory'    => [],
            'events'       => [],
            'achievements' => [],
        ];

        /** @var Item $item */
        foreach ($inventory->getItems() as $item) {
            $itemViewModel = $this->itemViewModelFactory->create($item);
            $itemViewModel->surface = $this->createSurface($item, $locationItemSurfaceInventories);
            $viewModel->inventory[] = $itemViewModel;
        }

        /** @var Event $event */
        foreach ($player->getEvents() as $event) {
            $viewModel->events[] = $this->eventViewModelFactory->create($event);
        }

        /** @var string $achievementId */
        foreach ($player->getAchievements() as $achievementId) {
            $viewModel->achievements[] = $this->achievementViewModelFactory->create($achievementId);
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
