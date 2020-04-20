<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
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

        $itemRepo = $this->itemRepoFactory->create($gameId);

        $player = $this->playerRepo->find($gameId);

        $isNewLocation = !in_array($locationId, $player->getEnteredLocations());
        $startingLocationId = $player->getLocationId();

        $player->move($locationId);

        if ($locationId === "the-street") {
            $player->kill();
            $event = $player->experienceEvent("shot-to-death");

        } elseif ($isNewLocation && $locationId === "landing") {
            $event = $player->experienceEvent("attic-noises");

        } elseif ($isNewLocation && $locationId === "attic") {
            $playerInventory = $itemRepo->findInventory(ItemWhereabouts::player());

            /** @var Item $item */
            foreach ($playerInventory->getItems() as $item) {
                if ($item->getTypeId() === "flashlight" && $item->getState() === "on") {
                    $event = $player->experienceEvent("an-illuminating-encounter");
                }
            }

            if (!isset($event)) {
                $event = $player->experienceEvent("a-brief-encounter");
            }

        } elseif (($startingLocationId === "front-garden" || $startingLocationId === "back-garden")
            && ($player->experiencedEvent("page-outside-first-try") || $player->experiencedEvent("page-outside-try-again"))
            && !$player->experiencedEvent("beam-in-first-glance")
            && !$player->experiencedEvent("beam-in-second-glance")
            && !$player->experiencedEvent("beam-in-first-glance-sandwich-retrieval")
            && !$player->experiencedEvent("beam-in-second-glance-sandwich-retrieval")
        ) {
            $player->move($startingLocationId);

            $locationInventory = $itemRepo->findInventory(ItemWhereabouts::location($startingLocationId));

            /** @var Item $item */
            foreach ($locationInventory->getItems() as $item) {
                if ($item->getTypeId() === "mysterious-sandwich") {
                    $mysteriousSandwich = $item;
                }
            }

            if (isset($mysteriousSandwich)) {
                $mysteriousSandwich->decrementQuantity();
                if ($player->experiencedEvent("a-brief-encounter")) {
                    $event = $player->experienceEvent("beam-in-first-glance-sandwich-retrieval");

                } elseif ($player->experiencedEvent("an-illuminating-encounter")) {
                    $event = $player->experienceEvent("beam-in-second-glance-sandwich-retrieval");
                }
            } else {
                if ($player->experiencedEvent("a-brief-encounter")) {
                    $event = $player->experienceEvent("beam-in-first-glance");

                } elseif ($player->experiencedEvent("an-illuminating-encounter")) {
                    $event = $player->experienceEvent("beam-in-second-glance");
                }
            }

            $item = $itemRepo->createType("covid-19-cure");
            $item->moveTo(ItemWhereabouts::location($startingLocationId));
            $item->incrementQuantity();
            $itemRepo->save($item);

            /** @var Item $item */
            foreach ($locationInventory->getItems() as $item) {
                $itemRepo->save($item);
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
