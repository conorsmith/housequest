<?php

use App\Domain\Inventory;
use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\LocationRepositoryConfig;
use App\Repositories\PlayerRepositoryDb;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Uuid;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$objects = include __DIR__ . "/../config/objects.php";

$locations = include __DIR__ . "/../config/locations.php";

Route::get("/", function () {
    return view("welcome");
});

Route::post("/new-game", function () use ($objects, $locations) {
    $gameId = Uuid::uuid4();

    DB::table("games")->insert([
        'id'         => $gameId,
        'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
    ]);

    DB::table("players")->insert([
        'id' => Uuid::uuid4(),
        'game_id' => $gameId,
        'location_id' => "hallway",
        'xp' => 0,
        'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
    ]);

    foreach ($locations as $locationId => $location) {
        if (array_key_exists('objects', $location)) {
            foreach ($location['objects'] as $key => $value) {
                if (is_string($value)) {
                    $itemId = $value;
                    $quantity = 1;
                } else {
                    $itemId = $key;
                    $quantity = $value;
                }

                $itemConfig = $objects[$itemId];

                if (array_key_exists('portions', $itemConfig)) {
                    $portions = $itemConfig['portions'];
                } else {
                    $portions = 1;
                }

                DB::table("objects")->insert([
                    'id'          => Uuid::uuid4(),
                    'game_id'     => $gameId,
                    'object_id'   => $itemId,
                    'location_id' => $locationId,
                    'quantity'    => $quantity,
                    'portions'    => $portions,
                    'created_at'  => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
                ]);
            }
        }
    }

    foreach ($objects as $containerId => $container) {
        if (array_key_exists('objects', $container)) {
            foreach ($container['objects'] as $key => $value) {
                if (is_string($value)) {
                    $itemId = $value;
                    $quantity = 1;
                } else {
                    $itemId = $key;
                    $quantity = $value;
                }

                $itemConfig = $objects[$itemId];

                if (array_key_exists('portions', $itemConfig)) {
                    $portions = $itemConfig['portions'];
                } else {
                    $portions = 1;
                }

                DB::table("objects")->insert([
                    'id'          => Uuid::uuid4(),
                    'game_id'     => $gameId,
                    'object_id'   => $itemId,
                    'location_id' => $containerId,
                    'quantity'    => $quantity,
                    'portions'    => $portions,
                    'created_at'  => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
                ]);
            }
        }
    }

    return redirect("/{$gameId}");
});

Route::get('/{gameId}', function ($gameId) use ($locations) {

    /** @var PlayerRepositoryDb $playerRepo */
    $playerRepo = app(PlayerRepositoryDb::class);

    /** @var ItemRepositoryDb $itemRepo */
    $itemRepo = app(ItemRepositoryDbFactory::class)->create(Uuid::fromString($gameId));

    $locationRepo = new LocationRepositoryConfig($locations);

    $player = $playerRepo->find(Uuid::fromString($gameId));
    $activeLocationItems = $itemRepo->findAtLocation($player->getLocationId());
    $inventory = $itemRepo->getInventory();

    $locationId = $player->getLocationId();

    $activeLocationConfig = $locationRepo->findForPlayer($player);

    $locationViewModel = (object) $activeLocationConfig;
    $locationViewModel->id = $locationId;
    $locationViewModel->egresses = [];
    $locationViewModel->objects = [];

    foreach ($activeLocationConfig['egresses'] as $egressLocationId) {
        $egressLocation = $locations[$egressLocationId];

        $locationViewModel->egresses[] = (object) [
            'id' => $egressLocationId,
            'label' => $egressLocation['title'],
        ];
    }

    /** @var Item $item */
    foreach ($activeLocationItems as $item) {
        $locationViewModel->objects[] = (object) [
            'id'                          => $item->getId(),
            'typeId'                      => $item->getTypeId(),
            'label'                       => $item->getName(),
            'quantity'                    => $item->getQuantity(),
            'hasAllPortions'              => $item->hasAllPortions(),
            'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            'isContainer'                 => $item->isContainer(),
            'isUsable'                    => $item->hasUse()
                && $item->getUse()->fromRoom()
        ];
    }

    $playerViewModel = (object) [
        'xp' => $player->getXp(),
        'inventory' => [],
    ];

    foreach ($inventory as $item) {
        $playerViewModel->inventory[] = (object) [
            'id'                          => $item->getId(),
            'typeId'                      => $item->getTypeId(),
            'label'                       => $item->getName(),
            'quantity'                    => $item->getQuantity(),
            'hasAllPortions'              => $item->hasAllPortions(),
            'remainingPortions'           => $item->getRemainingPortions(),
            'totalPortions'               => $item->getTotalPortions(),
            'remainingPortionsPercentage' => $item->getRemainingPortions() / $item->getTotalPortions() * 100,
            'isMultiPortionItem'          => $item->isMultiPortionItem(),
            'isEdible'                    => $item->isEdible(),
            'isContainer'                 => $item->isContainer(),
            'isUsable'                    => $item->hasUse()
                && $item->getUse()->fromInventory(),
        ];
    }

    $containerViewModels = [];

    /** @var Item $item */
    foreach ($activeLocationItems as $item) {
        if ($item->isContainer()) {
            $containerViewModel = (object) [
                'typeId'   => $item->getTypeId(),
                'label'    => $item->getName(),
                'contents' => [],
            ];

            $containedItems = $itemRepo->findAllInContainer($item);

            /** @var Item $containedItem */
            foreach ($containedItems as $containedItem) {
                $containerViewModel->contents[] = (object) [
                    'id'                          => $containedItem->getId(),
                    'typeId'                      => $containedItem->getTypeId(),
                    'label'                       => $containedItem->getName(),
                    'quantity'                    => $containedItem->getQuantity(),
                    'hasAllPortions'              => $containedItem->hasAllPortions(),
                    'remainingPortionsPercentage' => $containedItem->getRemainingPortions() / $containedItem->getTotalPortions() * 100,
                ];
            }

            $containerViewModels[] = $containerViewModel;
        }
    }

    return view('home', [
        'gameId' => $gameId,
        'location' => $locationViewModel,
        'player' => $playerViewModel,
        'containers' => $containerViewModels,
    ]);
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post("/{gameId}/go/{locationId}", function ($gameId, $locationId) {

    /** @var PlayerRepositoryDb $playerRepo */
    $playerRepo = app(PlayerRepositoryDb::class);

    $player = $playerRepo->find(Uuid::fromString($gameId));

    $player->move($locationId);

    $playerRepo->save($player);

    return redirect("/{$gameId}");
});

Route::post("/{gameId}/pick-up/{itemId}", function ($gameId, $itemId) {

    /** @var ItemRepositoryDb $itemRepo */
    $itemRepo = app(ItemRepositoryDbFactory::class)->create(Uuid::fromString($gameId));

    $playerInventory = new Inventory("player", $itemRepo->getInventory());

    $item = $itemRepo->find(Uuid::fromString($itemId));

    if ($item->getLocationId() === "player") {
        session()->flash("info", "You cannot pick up {$item->getName()}, you're already holding it.");
        return redirect("/{$gameId}");
    }

    $playerInventory->add($item);

    /** @var Item $item */
    foreach ($playerInventory->getItems() as $item) {
        $itemRepo->save($item);
    }

    return redirect("/{$gameId}");
});

Route::post("/{gameId}/drop/{itemId}/{locationId}", function ($gameId, $itemId, $locationId) {

    $itemId  = Uuid::fromString($itemId);

    /** @var ItemRepositoryDb $itemRepo */
    $itemRepo = app(ItemRepositoryDbFactory::class)->create(Uuid::fromString($gameId));

    $playerInventory = new Inventory("player", $itemRepo->getInventory());
    $locationInventory = new Inventory($locationId, $itemRepo->findAtLocation($locationId));

    if (is_null($playerInventory->find($itemId))) {
        $item = $locationInventory->find($itemId);
        session()->flash("info", "You cannot drop {$item->getName()}, you're not holding it.");
        return redirect("/{$gameId}");
    }

    $item = $playerInventory->remove($itemId);
    $locationInventory->add($item);

    /** @var Item $item */
    foreach ($playerInventory->getItems() as $item) {
        $itemRepo->save($item);
    }
    /** @var Item $item */
    foreach ($locationInventory->getItems() as $item) {
        $itemRepo->save($item);
    }

    return redirect("/{$gameId}");
});

Route::post("/{gameId}/eat/{itemId}", "PostEat");
Route::post("/{gameId}/use/{itemId}", "PostUse");
Route::post("/{gameId}/make", "PostMake");
Route::post("{gameId}/transfer/{containerId}", "PostTransfer");
