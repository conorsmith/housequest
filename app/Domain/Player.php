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
    private $events;

    /** @var array */
    private $achievements;

    /** @var array */
    private $eatenItemTypes;

    /** @var int */
    private $eatenItemsCount;

    /** @var array */
    private $enteredLocations;

    public function __construct(
        UuidInterface $id,
        string $name,
        string $locationId,
        int $xp,
        bool $isDead,
        bool $hasWon,
        array $events,
        array $achievements,
        array $eatenItemTypes,
        int $eatenItemCount,
        array $enteredLocations
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->locationId = $locationId;
        $this->xp = $xp;
        $this->isDead = $isDead;
        $this->hasWon = $hasWon;
        $this->events = $events;
        $this->achievements = $achievements;
        $this->eatenItemTypes = $eatenItemTypes;
        $this->eatenItemsCount = $eatenItemCount;
        $this->enteredLocations = $enteredLocations;
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

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function getEatenItemTypes(): array
    {
        return $this->eatenItemTypes;
    }

    public function getEatenItemsCount(): int
    {
        return $this->eatenItemsCount;
    }

    public function getEnteredLocations(): array
    {
        return $this->enteredLocations;
    }

    public function move(string $locationId): void
    {
        $this->locationId = $locationId;

        if (in_array($locationId, $this->enteredLocations)) {
            return;
        }

        $this->enteredLocations[] = $locationId;
    }

    public function kill(): void
    {
        $this->isDead = true;
    }

    public function win(): void
    {
        $this->hasWon = true;
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

    public function eat(Item $item): void
    {
        if (!$item->isIngestible()) {
            throw new DomainException("Player cannot eat non-ingestible item {$item->getTypeId()}.");
        }

        $this->eatenItemsCount++;

        if (in_array($item->getTypeId(), $this->eatenItemTypes)) {
            return;
        }

        $this->eatenItemTypes[] = $item->getTypeId();
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
}
