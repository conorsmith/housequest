<?php
declare(strict_types=1);

namespace App\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Player
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $locationId;

    /** @var int */
    private $xp;

    /** @var bool */
    private $isDead;

    /** @var bool */
    private $hasWon;

    /** @var array */
    private $conditions;

    /** @var array */
    private $events;

    /** @var array */
    private $achievements;

    /** @var array */
    private $eatenItemTypes;

    /** @var int */
    private $eatenItemsCount;

    /** @var array */
    private $enteredLocations;

    /** @var PlayerStats */
    private $stats;

    public function __construct(
        UuidInterface $id,
        string $name,
        string $locationId,
        int $xp,
        bool $isDead,
        bool $hasWon,
        array $conditions,
        array $events,
        array $achievements,
        array $eatenItemTypes,
        int $eatenItemCount,
        array $enteredLocations,
        PlayerStats $stats
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->locationId = $locationId;
        $this->xp = $xp;
        $this->isDead = $isDead;
        $this->hasWon = $hasWon;
        $this->conditions = $conditions;
        $this->events = $events;
        $this->achievements = $achievements;
        $this->eatenItemTypes = $eatenItemTypes;
        $this->eatenItemsCount = $eatenItemCount;
        $this->enteredLocations = $enteredLocations;
        $this->stats = $stats;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function isDead(): bool
    {
        return $this->isDead;
    }

    public function hasWon(): bool
    {
        return $this->hasWon;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function getStats(): PlayerStats
    {
        return $this->stats;
    }

    public function move(string $locationId): void
    {
        $this->locationId = $locationId;
        $this->stats->recordLocationEntered($locationId);
    }

    public function kill(): void
    {
        $this->isDead = true;
    }

    public function win(): void
    {
        $this->hasWon = true;
    }

    public function addCondition(string $condition): void
    {
        if (in_array($condition, $this->conditions)) {
            return;
        }

        $this->conditions[] = $condition;
    }

    public function removeCondition(string $removedCondition): void
    {
        foreach ($this->conditions as $key => $exisitngCondition) {
            if ($removedCondition === $exisitngCondition) {
                unset($this->conditions[$key]);
                return;
            }
        }
    }

    public function experienceEvent(string $eventId): ?Event
    {
        if ($this->experiencedEvent($eventId)) {
            return null;
        }

        $event = new Event($eventId, $this->locationId);

        $this->events[] = $event;

        return $event;
    }

    public function unlockAchievement(string $achievementId): bool
    {
        if (in_array($achievementId, $this->achievements)) {
            return false;
        }

        $this->achievements[] = $achievementId;

        return true;
    }

    public function useItem(Item $item): void
    {
        $this->stats->recordItemUsed($item);
    }

    public function useCombo(array $items): void
    {
        $this->stats->recordItemComboUsed($items);
    }

    public function eat(Item $item): void
    {
        if (!$item->isIngestible()) {
            throw new DomainException("Player cannot eat non-ingestible item {$item->getTypeId()}.");
        }

        $this->stats->recordItemEaten($item);
    }

    public function experiencedEvent(string $eventId): bool
    {
        /** @var Event $event */
        foreach ($this->events as $event) {
            if ($event->getId() === $eventId) {
                return true;
            }
        }

        return false;
    }

    public function unlockAchievements(): array
    {
        $existingAchievementIds = $this->getAchievements();

        if ($this->stats->getUsedItemsCount() === 5) {
            $this->unlockAchievement("use_count_5");
        } elseif ($this->stats->getUsedItemsCount() === 10) {
            $this->unlockAchievement("use_count_10");
        } elseif ($this->stats->getUsedItemsCount() === 25) {
            $this->unlockAchievement("use_count_25");
        } elseif ($this->stats->getUsedItemsCount() === 50) {
            $this->unlockAchievement("use_count_50");
        } elseif ($this->stats->getUsedItemsCount() === 100) {
            $this->unlockAchievement("use_count_100");
        }

        if ($this->stats->getEatenItemsCount() === 5) {
            $this->unlockAchievement("eat_count_5");
        } elseif ($this->stats->getEatenItemsCount() === 10) {
            $this->unlockAchievement("eat_count_10");
        } elseif ($this->stats->getEatenItemsCount() === 25) {
            $this->unlockAchievement("eat_count_25");
        } elseif ($this->stats->getEatenItemsCount() === 50) {
            $this->unlockAchievement("eat_count_50");
        }

        if (count($this->stats->getEatenItemTypes()) === 5) {
            $this->unlockAchievement("eat_types_5");
        } elseif (count($this->stats->getEatenItemTypes()) === 10) {
            $this->unlockAchievement("eat_types_10");
        } elseif (count($this->stats->getEatenItemTypes()) === 25) {
            $this->unlockAchievement("eat_types_25");
        } elseif (count($this->stats->getEatenItemTypes()) === 50) {
            $this->unlockAchievement("eat_types_50");
        } elseif (count($this->stats->getEatenItemTypes()) === 57) {
            $this->unlockAchievement("eat_types_57");
        }

        if (in_array("mouthwash", $this->stats->getUsedItemTypes())
            && in_array("dental-floss", $this->stats->getUsedItemTypes())
            && in_array(["sink", "toothbrush", "toothpaste"], $this->stats->getUsedItemCombos())
        ) {
            $this->unlockAchievement("use_class_teeth");
        }

        $unlockedAchievementIds = [];

        foreach ($this->achievements as $achievementId) {
            if (!in_array($achievementId, $existingAchievementIds)) {
                $unlockedAchievementIds[] = $achievementId;
            }
        }

        return $unlockedAchievementIds;
    }
}
