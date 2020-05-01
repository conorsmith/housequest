<?php
declare(strict_types=1);

namespace App\Domain;

final class PlayerStats
{
    /** @var array */
    private $usedItemTypes;

    /** @var int */
    private $usedItemsCount;

    /** @var array */
    private $usedItemCombos;

    /** @var int */
    private $usedItemCombosCount;

    /** @var array */
    private $eatenItemTypes;

    /** @var int */
    private $eatenItemsCount;

    /** @var array */
    private $enteredLocations;

    public function __construct(
        array $usedItemTypes,
        int $usedItemsCount,
        array $usedItemCombos,
        int $usedItemCombosCount,
        array $eatenItemTypes,
        int $eatenItemCount,
        array $enteredLocations
    ) {
        $this->usedItemTypes = $usedItemTypes;
        $this->usedItemsCount = $usedItemsCount;
        $this->usedItemCombos = $usedItemCombos;
        $this->usedItemCombosCount = $usedItemCombosCount;
        $this->eatenItemTypes = $eatenItemTypes;
        $this->eatenItemsCount = $eatenItemCount;
        $this->enteredLocations = $enteredLocations;
    }

    public function getUsedItemTypes(): array
    {
        return $this->usedItemTypes;
    }

    public function getUsedItemsCount(): int
    {
        return $this->usedItemsCount;
    }

    public function getUsedItemCombos(): array
    {
        return $this->usedItemCombos;
    }

    public function getUsedItemCombosCount(): int
    {
        return $this->usedItemCombosCount;
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

    public function recordItemUsed(Item $item): void
    {
        $this->usedItemsCount++;

        if (in_array($item->getTypeId(), $this->usedItemTypes)) {
            return;
        }

        $this->usedItemTypes[] = $item->getTypeId();
    }

    public function recordItemComboUsed(array $items): void
    {
        $this->usedItemCombosCount++;

        $itemTypes = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $itemTypes[] = $item->getTypeId();
        }

        sort($itemTypes);

        if (in_array($itemTypes, $this->usedItemCombos)) {
            return;
        }

        $this->usedItemCombos[] = $itemTypes;
    }

    public function recordItemEaten(Item $item): void
    {
        $this->eatenItemsCount++;

        if (in_array($item->getTypeId(), $this->eatenItemTypes)) {
            return;
        }

        $this->eatenItemTypes[] = $item->getTypeId();
    }

    public function recordLocationEntered(string $locationId): void
    {
        if (in_array($locationId, $this->enteredLocations)) {
            return;
        }

        $this->enteredLocations[] = $locationId;
    }
}
