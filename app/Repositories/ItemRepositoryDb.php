<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Item;
use App\Domain\ItemUse;
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

    public function find(string $id): ?Item
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

    public function findInInventory(Item $item): ?Item
    {
        $row = DB::selectOne(
            "SELECT * FROM objects WHERE game_id = ? AND location_id = ? AND object_id = ? AND portions = ?",
            [
                $this->gameId,
                "player",
                $item->getTypeId(),
                $item->getRemainingPortions(),
            ]
        );

        if (is_null($row)) {
            return null;
        }

        return $this->createItemFromRow($row);
    }

    public function findInContainer(string $containerId, Item $item): ?Item
    {
        $row = DB::selectOne(
            "SELECT * FROM objects WHERE game_id = ? AND location_id = ? AND object_id = ?",
            [
                $this->gameId,
                $containerId,
                $item->getTypeId(),
                $item->getRemainingPortions(),
            ]
        );

        if (is_null($row)) {
            return null;
        }

        return $this->createItemFromRow($row);
    }

    public function findOneOfType(string $typeId): ?Item
    {
        $row = DB::selectOne("SELECT * FROM objects WHERE game_id = ? AND object_id = ?", [
            $this->gameId,
            $typeId,
        ]);

        if (is_null($row)) {
            return null;
        }

        return $this->createItemFromRow($row);
    }

    public function getInventory(): array
    {
        $rows = DB::select("SELECT * FROM objects WHERE game_id = ? AND location_id = ? ORDER BY object_id ASC, portions DESC", [
            $this->gameId,
            "player",
        ]);

        return $this->createItemsFromRows($rows);
    }

    public function findAtLocation(string $locationId): array
    {
        $rows = DB::select("SELECT * FROM objects WHERE game_id = ? AND location_id = ? ORDER BY object_id ASC, portions DESC", [
            $this->gameId,
            $locationId,
        ]);

        return $this->createItemsFromRows($rows);
    }

    public function findAllInContainer(Item $container): array
    {
        $rows = DB::select("SELECT * FROM objects WHERE game_id = ? AND location_id = ? ORDER BY object_id ASC, portions DESC", [
            $this->gameId,
            $container->getTypeId(),
        ]);

        return $this->createItemsFromRows($rows);
    }

    public function findMultiple(array $ids): array
    {
        $whereExpressions = array_fill(0, count($ids), "id = ?");
        $whereString = implode(" OR ", $whereExpressions);

        $rows = DB::select(
            "SELECT * FROM objects WHERE ({$whereString})",
            $ids
        );

        return $this->createItemsFromRows($rows);
    }

    public function createForInventory(string $itemId): Item
    {
        return $this->createItemFromRow((object)([
            'id'          => Uuid::uuid4()->toString(),
            'object_id'   => $itemId,
            'location_id' => "player",
            'quantity'    => 0,
            'portions'    => 1,
        ]));
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
                        'location_id' => $item->getLocationId(),
                        'quantity'    => $item->getQuantity(),
                        'portions'    => $item->getRemainingPortions(),
                        'created_at'  => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
                    ]);
            } else {
                DB::table("objects")
                    ->where([
                        'id' => $item->getId(),
                    ])
                    ->update([
                        'location_id' => $item->getLocationId(),
                        'quantity'    => $item->getQuantity(),
                        'portions'    => $item->getRemainingPortions(),
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

        return new Item(
            Uuid::fromString($row->id),
            $row->object_id,
            $itemConfig['name'],
            $row->location_id,
            intval($row->quantity),
            intval($row->portions),
            $totalPortions,
            Arr::get($itemConfig, 'attributes', []),
            $itemUse
        );
    }
}
