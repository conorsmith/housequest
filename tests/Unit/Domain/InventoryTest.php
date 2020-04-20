<?php
declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Domain\RecipeIngredient;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class InventoryTest extends TestCase
{
    /**
     * @test
     */
    function eat_a_single_portion_item()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item = $this->createIngestibleItem($itemId, 1, 1, 1)
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 0
                        && $item->getRemainingPortions() === 1;
                },
            ]
        );
    }

    /**
     * @test
     */
    function eat_a_multiple_portion_item()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item = $this->createIngestibleItem($itemId, 1, 100, 100)
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 1
                        && $item->getRemainingPortions() === 99;
                },
            ]
        );
    }

    /**
     * @test
     */
    function eat_final_portion_of_a_multiple_portion_item()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item = $this->createIngestibleItem($itemId, 1, 1, 100)
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 1
                        && $item->getRemainingPortions() === 0;
                },
            ]
        );
    }

    /**
     * @test
     */
    function eat_multiple_portion_item_with_one_more_portion_than_another_inventory_item()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item      = $this->createIngestibleItem($itemId, 1, 50, 100),
            $otherItem = $this->createIngestibleItem(Uuid::uuid4(), 1, 49, 100),
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 0
                        && $item->getRemainingPortions() === 0;
                },
                function (Item $item) {
                    return $item->getQuantity() === 2
                        && $item->getRemainingPortions() === 49;
                },
            ]
        );
    }

    /**
     * @test
     */
    function eat_multiple_portion_item_with_multiple_quantities()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item = $this->createIngestibleItem($itemId, 100, 50, 100),
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 99
                        && $item->getRemainingPortions() === 50;
                },
                function (Item $item) {
                    return $item->getQuantity() === 1
                        && $item->getRemainingPortions() === 49;
                },
            ]
        );
    }

    /**
     * @test
     */
    function eat_multiple_portion_item_with_multiple_quantities_one_more_portion_than_another_inventory_item()
    {
        $itemId = Uuid::uuid4();

        $inventory = new Inventory(ItemWhereabouts::player(), [
            $item      = $this->createIngestibleItem($itemId, 100, 50, 100),
            $otherItem = $this->createIngestibleItem(Uuid::uuid4(), 1, 49, 100)
        ]);

        $inventory->removeEatenItem($itemId);

        $this->assertInventoryContains(
            $inventory,
            [
                function (Item $item) {
                    return $item->getQuantity() === 99
                        && $item->getRemainingPortions() === 50;
                },
                function (Item $item) {
                    return $item->getQuantity() === 2
                        && $item->getRemainingPortions() === 49;
                },
                function (Item $item) {
                    return $item->getQuantity() === 0
                        && $item->getRemainingPortions() === 0;
                },
            ]
        );
    }

    private function createIngestibleItem(UuidInterface $id, int $quantity, int $remainingPortions, int $totalPortions): Item
    {
        return new Item(
            $id,
            "some-item-type",
            ItemWhereabouts::player(),
            $quantity,
            $remainingPortions,
            $totalPortions,
            null,
            [
                "ingestible",
            ],
            null
        );
    }

    private function assertInventoryContains(Inventory $inventory, array $expectedItemCallbacks)
    {
        $this->assertThat(
            $inventory->getItems(),
            $this->callback(function (array $items) use ($expectedItemCallbacks) {
                $expectationsMet = 0;

                /** @var Item $item */
                foreach ($items as $item) {
                    foreach ($expectedItemCallbacks as $expectedItemCallback) {
                        if ($expectedItemCallback($item)) {
                            $expectationsMet++;
                        }
                    }
                }

                return $expectationsMet === count($items);
            })
        );
    }
}
