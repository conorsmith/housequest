<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Event;
use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class PostNewGame extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var ItemRepositoryDbFactory */
    private $itemRepoFactory;

    public function __construct(PlayerRepository $playerRepo, ItemRepositoryDbFactory $itemRepoFactory)
    {
        $this->playerRepo = $playerRepo;
        $this->itemRepoFactory = $itemRepoFactory;
    }

    public function __invoke(Request $request)
    {
        $objects = include __DIR__ . "/../../../config/objects.php";

        $locations = include __DIR__ . "/../../../config/locations.php";

        $events = include __DIR__ . "/../../../config/events.php";

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

        $player = new Player(
            $playerId,
            $name,
            "master-bedroom",
            0,
            false,
            false,
            [
                new Event("start", "master-bedroom"),
            ],
            [],
            [],
            0,
            [
                "master-bedroom",
            ]
        );

        $locationInventories = [];
        $containerInventories = [];

        foreach ($locations as $locationId => $location) {
            if (array_key_exists('objects', $location)) {
                $locationInventories[$locationId] = new Inventory($locationId, []);
                foreach ($location['objects'] as $key => $value) {
                    if (is_string($value)) {
                        $itemTypeId = $value;
                        $quantity = 1;
                        $contents = [];
                    } elseif (is_array($value)) {
                        $itemTypeId = $value['id'];
                        $quantity = Arr::get($value, 'quantity', 1);
                        $contents = Arr::get($value, 'contents', []);
                    } else {
                        $itemTypeId = $key;
                        $quantity = $value;
                        $contents = [];
                    }

                    $itemConfig = $objects[$itemTypeId];

                    if (array_key_exists('portions', $itemConfig)) {
                        $portions = $itemConfig['portions'];
                    } else {
                        $portions = 1;
                    }

                    $item = new Item(
                        $itemId = Uuid::uuid4(),
                        $itemTypeId,
                        $itemConfig['name'],
                        $locationId,
                        $quantity,
                        $portions,
                        $portions,
                        Arr::get($itemConfig, 'attributes', []),
                        null
                    );

                    $locationInventories[$locationId]->add($item);

                    if (count($contents) > 0) {
                        $containerInventories[$itemId->toString()] = new Inventory($itemId->toString(), []);
                    }

                    foreach ($contents as $contentsKey => $contentsValue) {

                        if (is_int($contentsValue)) {
                            $containedItemTypeId = $contentsKey;
                            $quantity = $contentsValue;
                        } else {
                            $containedItemTypeId = $contentsValue;
                            $quantity = 1;
                        }

                        $itemConfig = $objects[$containedItemTypeId];

                        if (array_key_exists('portions', $itemConfig)) {
                            $portions = $itemConfig['portions'];
                        } else {
                            $portions = 1;
                        }

                        $containedItem = new Item(
                            Uuid::uuid4(),
                            $containedItemTypeId,
                            $itemConfig['name'],
                            $itemId->toString(),
                            $quantity,
                            $portions,
                            $portions,
                            Arr::get($itemConfig, 'attributes', []),
                            null
                        );

                        $containerInventories[$itemId->toString()]->add($containedItem);
                    }
                }
            }
        }

        $this->playerRepo->create($gameId, $player);

        /** @var Inventory $inventory */
        foreach ($locationInventories as $inventory) {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                $itemRepo->save($item);
            }
        }

        /** @var Inventory $inventory */
        foreach ($containerInventories as $inventory) {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                $itemRepo->save($item);
            }
        }

        session()->flash("message", $events['start']['message']);
        return redirect("/{$gameId}");
    }
}
