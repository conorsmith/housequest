<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\PlayerRepository;
use App\ViewModels\EventFactory;
use Ramsey\Uuid\Uuid;

final class PostGo extends Controller
{
    /** @var PlayerRepository */
    private $playerRepo;

    /** @var EventFactory */
    private $eventViewModelFactory;

    public function __construct(PlayerRepository $playerRepo, EventFactory $eventViewModelFactory)
    {
        $this->playerRepo = $playerRepo;
        $this->eventViewModelFactory = $eventViewModelFactory;
    }

    public function __invoke(string $gameId, string $locationId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        $isNewLocation = !in_array($locationId, $player->getEnteredLocations());

        $player->move($locationId);

        if ($locationId === "the-street") {
            $player->kill();
            $player->experienceEvent("shot-to-death");
            session()->flash("message", $this->eventViewModelFactory->createMessage("shot-to-death"));
        }

        if ($isNewLocation && $locationId === "landing") {
            $player->experienceEvent("attic-noises");
            session()->flash("message", $this->eventViewModelFactory->createMessage("attic-noises"));
        }

        if ($isNewLocation && $locationId === "attic") {
            $player->experienceEvent("a-brief-encounter");
            session()->flash("message", $this->eventViewModelFactory->createMessage("a-brief-encounter"));
        }

        $this->playerRepo->save($player);

        return redirect("/{$gameId}");
    }
}
