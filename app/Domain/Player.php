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
    private $locationId;

    /** @var int */
    private $xp;

    /** @var array */
    private $eatenItemTypes;

    /** @var int */
    private $eatenItemsCount;

    public function __construct(
        UuidInterface $id,
        string $locationId,
        int $xp,
        array $eatenItemTypes,
        int $eatenItemCount
    ) {
        $this->id = $id;
        $this->locationId = $locationId;
        $this->xp = $xp;
        $this->eatenItemTypes = $eatenItemTypes;
        $this->eatenItemsCount = $eatenItemCount;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function getEatenItemTypes(): array
    {
        return $this->eatenItemTypes;
    }

    public function getEatenItemsCount(): int
    {
        return $this->eatenItemsCount;
    }

    public function move(string $locationId): void
    {
        $this->locationId = $locationId;
    }

    public function gainXp(int $gain): void
    {
        $this->xp += $gain;
    }

    public function eat(Item $item): void
    {
        if (!$item->isEdible()) {
            throw new DomainException("Player cannot eat inedible item {$item->getTypeId()}.");
        }

        $this->eatenItemsCount++;

        if (in_array($item->getTypeId(), $this->eatenItemTypes)) {
            return;
        }

        $this->eatenItemTypes[] = $item->getTypeId();
    }
}
