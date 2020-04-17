<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class PostNewGame extends Controller
{
    public function __invoke(Request $request)
    {
        $objects = include __DIR__ . "/../../../config/objects.php";

        $locations = include __DIR__ . "/../../../config/locations.php";

        $events = include __DIR__ . "/../../../config/events.php";

        $gameId = Uuid::uuid4();
        $playerId = Uuid::uuid4();

        DB::table("games")->insert([
            'id'         => $gameId,
            'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
        ]);

        $name = $request->input("playerName");

        if (is_null($name)) {
            session()->flash("error", "You must enter a name to start a new game");
            return redirect("/");
        }

        DB::table("players")->insert([
            'id' => $playerId,
            'game_id' => $gameId,
            'name' => $name,
            'location_id' => "master-bedroom",
            'xp' => 0,
            'is_dead' => false,
            'has_won' => false,
            'eaten_items_count' => 0,
            'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
        ]);

        DB::table("player_location_action_log")->insert([
            'id' => Uuid::uuid4(),
            'player_id' => $playerId,
            'location_id' => "master-bedroom",
            'action' => "entered",
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

        DB::table("player_event_log")->insert([
            'id' => Uuid::uuid4(),
            'player_id' => $playerId,
            'event_id' => "start",
            'location_id' => "master-bedroom",
            'created_at' => Carbon::now("Europe/Dublin")->format("Y-m-d H:i:s"),
        ]);

        session()->flash("message", $events['start']['message']);
        return redirect("/{$gameId}");
    }
}
