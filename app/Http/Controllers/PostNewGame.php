<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Event;
use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Domain\Player;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\EventFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PostNewGame extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDbFactory */
    private $itemRepoFactory;

    /** @var EventFactory */
    private $eventViewModelFactory;

    public function __construct(
        PlayerRepository $playerRepo,
        ItemRepositoryDbFactory $itemRepoFactory,
        EventFactory $eventViewModelFactory
    ) {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
        $this->eventViewModelFactory = $eventViewModelFactory;
    }

    public function __invoke(Request $request)
    {
        $gameId = Uuid::uuid4();
        $playerId = Uuid::uuid4();

        $itemRepo = $this->itemRepoFactory->create($gameId);

        DB::table("games")->insert([
            'id'         => $gameId,
            'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
        ]);

        $name = $request->input("playerName");

        if (is_null($name)) {
            session()->flash("error", "You must enter a name to start a new game");
            return redirect("/");
        }

        $startingLocationId = "master-bedroom";
        $startEvent = new Event("start", $startingLocationId);

        $player = new Player(
            $playerId,
            $name,
            $startingLocationId,
            0,
            false,
            false,
            [
                $startEvent,
            ],
            [],
            [],
            0,
            [
                $startingLocationId,
            ]
        );

        $inventories = $this->createInventoriesForGame($gameId);

        $this->playerRepo->saveNew($gameId, $player);

        /** @var Inventory $inventory */
        foreach ($inventories as $inventory) {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                $itemRepo->save($item);
            }
        }

        $eventViewModel = $this->eventViewModelFactory->create($startEvent);
        session()->flash("message", $eventViewModel->message);
        return redirect("/{$gameId}");
    }

    private function createInventoriesForGame(UuidInterface $gameId)
    {
        $locations = include __DIR__ . "/../../../config/locations.php";

        $itemRepo = $this->itemRepoFactory->create($gameId);

        $inventories = [];

        foreach ($locations as $locationId => $location) {
            if (array_key_exists('items', $location)) {
                $locationInventory = new Inventory(ItemWhereabouts::location($locationId), []);
                foreach ($location['items'] as $key => $value) {
                    if (is_string($value)) {
                        $itemTypeId = $value;
                        $quantity = 1;
                        $contents = [];
                        $surface = [];
                    } elseif (is_array($value)) {
                        $itemTypeId = $value['id'];
                        $quantity = Arr::get($value, 'quantity', 1);
                        $contents = Arr::get($value, 'contents', []);
                        $surface = Arr::get($value, 'surface', []);
                    } else {
                        $itemTypeId = $key;
                        $quantity = $value;
                        $contents = [];
                        $surface = [];
                    }

                    $item = $itemRepo->createType($itemTypeId);
                    $item->moveTo(ItemWhereabouts::location($locationId));
                    $item->addQuantity($quantity);

                    $locationInventory->add($item);

                    if (count($contents) > 0) {
                        $containerInventory = new Inventory(ItemWhereabouts::itemContents($item->getId()->toString()), []);

                        foreach ($contents as $contentsKey => $contentsValue) {

                            if (is_int($contentsValue)) {
                                $containedItemTypeId = $contentsKey;
                                $quantity = $contentsValue;
                            } else {
                                $containedItemTypeId = $contentsValue;
                                $quantity = 1;
                            }

                            $containedItem = $itemRepo->createType($containedItemTypeId);
                            $containedItem->moveTo(ItemWhereabouts::itemContents($item->getId()->toString()));
                            $containedItem->addQuantity($quantity);

                            $containerInventory->add($containedItem);
                        }

                        $inventories[] = $containerInventory;
                    }

                    if (count($surface) > 0) {
                        $surfaceInventory = new Inventory(ItemWhereabouts::itemSurface($item->getId()->toString()), []);

                        foreach ($surface as $surfaceKey => $surfaceValue) {

                            if (is_int($surfaceValue)) {
                                $containedItemTypeId = $surfaceKey;
                                $quantity = $surfaceValue;
                            } else {
                                $containedItemTypeId = $surfaceValue;
                                $quantity = 1;
                            }

                            $containedItem = $itemRepo->createType($containedItemTypeId);
                            $containedItem->moveTo(ItemWhereabouts::itemSurface($item->getId()->toString()));
                            $containedItem->addQuantity($quantity);

                            $surfaceInventory->add($containedItem);
                        }

                        $inventories[] = $surfaceInventory;
                    }
                }
                $inventories[] = $locationInventory;
            }
        }

        return $inventories;
    }
}
