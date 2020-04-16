<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\EventRepositoryConfig;
use App\Repositories\PlayerRepository;
use Ramsey\Uuid\Uuid;

final class PostGo extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var EventRepositoryConfig */
    private $eventRepo;

    public function __construct(PlayerRepository $playerRepo, EventRepositoryConfig $eventRepo)
    {
        $this->playerRepo = $playerRepo;
        $this->eventRepo = $eventRepo;
    }

    public function __invoke(string $gameId, string $locationId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        $isNewLocation = !in_array($locationId, $player->getEnteredLocations());

        $player->move($locationId);

        if ($locationId === "the-street") {
            $player->kill();
            $player->experienceEvent("shot-to-death");
            session()->flash("message", $this->eventRepo->findMessage("shot-to-death"));
        }

        if ($isNewLocation && $locationId === "landing") {
            $player->experienceEvent("attic-noises");
            session()->flash("message", $this->eventRepo->findMessage("attic-noises"));
        }

        if ($isNewLocation && $locationId === "attic") {
            $player->experienceEvent("a-brief-encounter");
            session()->flash("message", $this->eventRepo->findMessage("a-brief-encounter"));
        }

        $this->playerRepo->save($player);

        return redirect("/{$gameId}");
    }
}
