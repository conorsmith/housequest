<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Event;
use App\Domain\InventoryTreeNode;
use App\Domain\Player;
use stdClass;

final class PlayerFactory
{
    /** @var EventFactory */
    private $eventViewModelFactory;

    /** @var AchievementFactory */
    private $achievementViewModelFactory;

    /** @var InventoryFactory */
    private $inventoryViewModelFactory;

    public function __construct(
        EventFactory $eventViewModelFactory,
        AchievementFactory $achievementViewModelFactory,
        InventoryFactory $inventoryViewModelFactory
    ) {
        $this->eventViewModelFactory = $eventViewModelFactory;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
        $this->inventoryViewModelFactory = $inventoryViewModelFactory;
    }

    public function create(Player $player, InventoryTreeNode $inventoryTree): stdClass
    {
        $viewModel = (object) [
            'name'         => $player->getName(),
            'isDead'       => $player->isDead(),
            'hasWon'       => $player->hasWon(),
            'inventory'    => $this->inventoryViewModelFactory->fromInventoryTree($inventoryTree),
            'events'       => [],
            'achievements' => [],
        ];

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
}
