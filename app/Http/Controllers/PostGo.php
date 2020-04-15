<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class PostGo extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    public function __construct(PlayerRepository $playerRepo)
    {
        $this->playerRepo = $playerRepo;
    }

    public function __invoke(string $gameId, string $locationId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        $isNewLocation = !in_array($locationId, $player->getEnteredLocations());

        $player->move($locationId);

        if ($locationId === "the-street") {
            $player->kill();

            session()->flash(
                "message",
                "You foolishly step out from your front garden into the street and are immediately torn asunder in a hail of bullets by a passing military patrol drone."
            );
        }

        if ($isNewLocation && $locationId === "landing") {
            session()->flash(
                "message",
                "You hear a strange noise. It's faint, but you can tell that it's coming from the attic. The sounds are disturbing, but this is the first interesting thing that's happened in weeks. How will you get up there?"
            );
        }

        if ($isNewLocation && $locationId === "attic") {
            session()->flash(
                "message",
                "Your make your way up into the attic and look around. You hear a haunting squeal and are briefly blinded by a bright flash of light. After regaining your vision you realise that whatever had been up here is now gone. It looks like whatever it was dropped something..."
            );
        }

        $this->playerRepo->save($player);

        return redirect("/{$gameId}");
    }
}
