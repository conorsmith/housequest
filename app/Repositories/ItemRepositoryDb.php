<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\ItemUse;
use App\Domain\ItemWhereabouts;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;

final class ItemRepositoryDb implements ItemRepository
{
    /** @var UuidInterface */
    private $gameId;

    /** @var array */
    private $config;

    public function __construct(UuidInterface $gameId, array $config)
    {
        $this->gameId = $gameId;
        $this->config = $config;
    }

    public function find(UuidInterface $id): ?Item
    {
        $row = DB::selectOne("SELECT * FROM objects WHERE game_id = ? AND id = ?", [
            $this->gameId,
            $id,
        ]);

        if (is_null($row)) {
            return null;
        }

        return $this->createItemFromRow($row);
    }

    public function findInventory(ItemWhereabouts $whereabouts): Inventory
    {
        $rows = DB::select(
            "SELECT * FROM objects WHERE game_id = ? AND location_id = ? ORDER BY object_id ASC, portions DESC",
            [
                $this->gameId,
                $whereabouts->__toString(),
            ]
        );

        $items = $this->createItemsFromRows($rows);

        return new Inventory($whereabouts, $items);
    }

    public function createType(string $itemTypeId): Item
    {
        $itemConfig = $this->config[$itemTypeId];

        if (array_key_exists('portions', $itemConfig)) {
            $portions = $itemConfig['portions'];
        } else {
            $portions = 1;
        }

        if (array_key_exists('states', $itemConfig)) {
            $state = array_key_first($itemConfig['states']);
        } else {
            $state = null;
        }

        return $this->createItemFromRow((object) [
            'id'          => Uuid::uuid4()->toString(),
            'object_id'   => $itemTypeId,
            'location_id' => "",
            'quantity'    => 0,
            'portions'    => $portions,
            'state'       => $state,
        ]);
    }

    public function save(Item $item): void
    {
        if ($item->isDepleted()) {
            DB::table("objects")
                ->where([
                    'id' => $item->getId(),
                ])
                ->delete();
        } else {
            $itemRow = DB::selectOne("SELECT * FROM objects WHERE id = ?", [
                $item->getId()->toString(),
            ]);

            if (is_null($itemRow)) {
                DB::table("objects")
                    ->insert([
                        'id'          => $item->getId(),
                        'game_id'     => $this->gameId,
                        'object_id'   => $item->getTypeId(),
                        'location_id' => $item->getWhereabouts()->__toString(),
                        'quantity'    => $item->getQuantity(),
                        'portions'    => $item->getRemainingPortions(),
                        'state'       => $item->getState(),
                        'created_at'  => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
                    ]);
            } else {
                DB::table("objects")
                    ->where([
                        'id' => $item->getId(),
                    ])
                    ->update([
                        'location_id' => $item->getWhereabouts()->__toString(),
                        'quantity'    => $item->getQuantity(),
                        'portions'    => $item->getRemainingPortions(),
                        'state'       => $item->getState(),
                    ]);
            }
        }
    }

    private function createItemsFromRows(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->createItemFromRow($row);
        }

        return $items;
    }

    private function createItemFromRow(stdClass $row): Item
    {
        $itemConfig = $this->config[$row->object_id];

        if (array_key_exists('use', $itemConfig)) {
            if (is_array($itemConfig['use'])) {
                $itemUse = new ItemUse(
                    Arr::get($itemConfig['use'], 'location', []),
                    $itemConfig['use']['message']
                );
            } else {
                $itemUse = new ItemUse(
                    [],
                    $itemConfig['use']
                );
            }
        } else {
            $itemUse = null;
        }

        if (array_key_exists('portions', $itemConfig)) {
            $totalPortions = $itemConfig['portions'];
        } else {
            $totalPortions = 1;
        }

        $attributes = Arr::get($itemConfig, 'attributes', []);

        if (array_key_exists('states', $itemConfig)) {
            $state = $itemConfig['states'][$row->state];
            if (is_array($state)
                && array_key_exists('attributes', $state)
            ) {
                $attributes = array_merge($attributes, $state['attributes']);
            }
        }

        return new Item(
            Uuid::fromString($row->id),
            $row->object_id,
            new ItemWhereabouts($row->location_id),
            intval($row->quantity),
            intval($row->portions),
            $totalPortions,
            $row->state,
            $attributes,
            $itemUse
        );
    }
}
