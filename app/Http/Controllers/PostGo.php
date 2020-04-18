<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\ViewModels\EventFactory;
use Ramsey\Uuid\Uuid;

final class PostGo extends Controller
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

    public function __invoke(string $gameId, string $locationId)
    {
        $gameId = Uuid::fromString($gameId);

        $player = $this->playerRepo->find($gameId);

        $isNewLocation = !in_array($locationId, $player->getEnteredLocations());

        $player->move($locationId);

        if ($locationId === "the-street") {
            $player->kill();
            $event = $player->experienceEvent("shot-to-death");

        } elseif ($isNewLocation && $locationId === "landing") {
            $event = $player->experienceEvent("attic-noises");

        } elseif ($isNewLocation && $locationId === "attic") {
            $playerInventory = $this->itemRepoFactory->create($gameId)->findInventory("player");

            /** @var Item $item */
            foreach ($playerInventory->getItems() as $item) {
                if ($item->getTypeId() === "flashlight" && $item->getState() === "on") {
                    $event = $player->experienceEvent("an-illuminating-encounter");
                }
            }

            if (!isset($event)) {
                $event = $player->experienceEvent("a-brief-encounter");
            }
        } else {
            $event = null;
        }

        $this->playerRepo->save($player);

        if (!is_null($event)) {
            session()->flash("messageRaw", $this->eventViewModelFactory->create($event)->message);
        }

        return redirect("/{$gameId}");
    }
}
