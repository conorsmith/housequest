<?php
declare(strict_types=1);

namespace App\Domain;

use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Item
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $typeId;

    /** @var string */
    private $locationId;

    /** @var int */
    private $quantity;

    /** @var int */
    private $remainingPortions;

    /** @var int */
    private $totalPortions;

    /** @var ?string */
    private $state;

    /** @var array */
    private $attributes;

    /** @var ?ItemUse */
    private $use;

    public function __construct(
        UuidInterface $id,
        string $typeId,
        string $locationId,
        int $quantity,
        int $remainingPortions,
        int $totalPortions,
        ?string $state,
        array $attributes,
        ?ItemUse $use
    ) {
        $this->id = $id;
        $this->typeId = $typeId;
        $this->locationId = $locationId;
        $this->quantity = $quantity;
        $this->remainingPortions = $remainingPortions;
        $this->totalPortions = $totalPortions;
        $this->state = $state;
        $this->attributes = $attributes;
        $this->use = $use;
    }

    public function split(int $quantity): self
    {
        $this->quantity -= $quantity;

        return new self(
            Uuid::uuid4(),
            $this->typeId,
            $this->locationId,
            $quantity,
            $this->remainingPortions,
            $this->totalPortions,
            $this->state,
            $this->attributes,
            $this->use
        );
    }

    public function createEmptyCopy(): self
    {
        return new self(
            Uuid::uuid4(),
            $this->typeId,
            $this->locationId,
            0,
            $this->remainingPortions,
            $this->totalPortions,
            $this->state,
            $this->attributes,
            $this->use
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getRemainingPortions(): int
    {
        return $this->remainingPortions;
    }

    public function getTotalPortions(): int
    {
        return $this->totalPortions;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getUse(): ItemUse
    {
        return $this->use;
    }

    public function isDepleted(): bool
    {
        return $this->quantity === 0
            || $this->quantity === 1 && $this->remainingPortions === 0;
    }

    public function isContainer(): bool
    {
        return in_array("container", $this->attributes);
    }

    public function isEdible(): bool
    {
        return in_array("edible", $this->attributes);
    }

    public function isAffixed(): bool
    {
        return in_array("affixed", $this->attributes);
    }

    public function isHeavy(): bool
    {
        return in_array("heavy", $this->attributes);
    }

    public function isDangerous(): bool
    {
        return in_array("dangerous", $this->attributes);
    }

    public function isSinglePortionItem(): bool
    {
        return $this->totalPortions === 1;
    }

    public function isMultiPortionItem(): bool
    {
        return $this->totalPortions > 1;
    }

    public function hasManyQuantities(): bool
    {
        return $this->quantity > 1;
    }

    public function hasAllPortions(): bool
    {
        return $this->remainingPortions === $this->totalPortions;
    }

    public function hasUse(): bool
    {
        return !is_null($this->use);
    }

    public function canMergeWith(self $other): bool
    {
        return $this->id !== $other->id
            && $this->typeId === $other->typeId
            && !$this->isContainer()
            && $this->remainingPortions === $other->remainingPortions
            && $this->locationId === $other->locationId;
    }

    public function moveTo(string $locationId): void
    {
        $this->locationId = $locationId;
    }

    public function merge(self $other): void
    {
        $this->quantity += $other->quantity;

        $other->quantity = 0;
        $other->remainingPortions = 0;
    }

    public function transitionState(string $newState): void
    {
        $this->state = $newState;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        $this->quantity--;
    }

    public function addQuantity(int $quantity): void
    {
        $this->quantity += $quantity;
    }

    public function removeQuantity(int $quantity): void
    {
        if ($quantity > $this->quantity) {
            throw new DomainException("Cannot remove {$quantity} items. Only {$this->quantity} available.");
        }

        $this->quantity -= $quantity;
    }

    public function removePortions(int $portions): void
    {
        if ($portions > $this->remainingPortions) {
            throw new DomainException("Cannot remove {$portions} portions. Only {$this->remainingPortions} available.");
        }

        $this->remainingPortions -= $portions;
    }

    public function reduceQuantityTo(int $quantity): void
    {
        if ($quantity > $this->quantity) {
            throw new DomainException(
                "Cannot reduce quantity to {$quantity}. Existing quantity is {$this->quantity}."
            );
        }

        $this->quantity = $quantity;
    }
}
