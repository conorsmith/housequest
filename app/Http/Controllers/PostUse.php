<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\EventRepositoryConfig;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostUse extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    /** @var EventRepositoryConfig */
    private $eventRepo;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory, PlayerRepositoryDb $playerRepo, EventRepositoryConfig $eventRepo)
    {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->eventRepo = $eventRepo;
    }

    public function __invoke(Request $request, string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $item = $itemRepo->find($itemId);

        if ($this->hasCustomUse($item)) {
            $this->executeCustomUse($request, $player, $item, $itemRepo);
            return redirect("/{$gameId}");
        }

        if (!$item->hasUse()) {
            session()->flash("info", "That did nothing.");
            return redirect("/{$gameId}");
        }

        $use = $item->getUse();

        $this->playerRepo->save($player);

        session()->flash("success", $use->getMessage());
        return redirect("/{$gameId}");
    }

    private const CUSTOM_USES = [
        'step-ladder'                 => "useStepLadder",
        'deployed-step-ladder'        => "useDeployedStepLadder",
        'quarantine-extension-notice' => "useQuarantineExtensionNotice",
        'covid-19-cure'               => "useCovid19Cure",
        'telephone'                   => "useTelephone",
        'bed'                         => "useBed",
    ];

    private function hasCustomUse(Item $item): bool
    {
        return array_key_exists($item->getTypeId(), self::CUSTOM_USES);
    }

    private function executeCustomUse(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $customUseFunction = self::CUSTOM_USES[$item->getTypeId()];
        $this->$customUseFunction($request, $player, $item, $itemRepo);
    }

    private function useStepLadder(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $ladderInventory = new Inventory($item->getLocationId(), $itemRepo->findAtLocation($item->getLocationId()));
        if ($item->getLocationId() !== $player->getLocationId()) {
            $locationInventory = new Inventory($player->getLocationId(), $itemRepo->findAtLocation($player->getLocationId()));
        } else {
            $locationInventory = $ladderInventory;
        }

        /** @var Item $inventoryItem */
        foreach ($ladderInventory->getItems() as $inventoryItem) {
            if ($inventoryItem->getId()->equals($item->getId())) {
                $inventoryItem->decrementQuantity();
            }
        }

        $item = $itemRepo->createForInventory("deployed-step-ladder");
        $item->moveTo($player->getLocationId());
        $item->incrementQuantity();
        $locationInventory->add($item);

        /** @var Item $inventoryItem */
        foreach ($ladderInventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        /** @var Item $inventoryItem */
        foreach ($locationInventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        session()->flash("success", "You deployed the Step Ladder.");
    }

    private function useDeployedStepLadder(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $inventory = new Inventory($item->getLocationId(), $itemRepo->findAtLocation($item->getLocationId()));

        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            if ($inventoryItem->getId()->equals($item->getId())) {
                $inventoryItem->decrementQuantity();
            }
        }

        $alteredItem = $itemRepo->createForInventory("step-ladder");
        $alteredItem->moveTo($item->getLocationId());
        $alteredItem->incrementQuantity();
        $inventory->add($alteredItem);

        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        session()->flash("success", "You closed the Step Ladder.");
    }

    private function useQuarantineExtensionNotice(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        session()->flash(
            "messageRaw",
            "<p><strong>QUARANTINE EXTENSION NOTICE</strong></p>"
            . "<p>The existing quarantine protocol is being extended by another month. Please remain in your home at all times.</p>"
            . "<p>Failure to comply with the quarantine protocol will result in a fine not exceeding €1,000 and/or immediate execution.</p>"
            . "<p>Any queries can be made to 0800 2684 319</p>"
            . "<p class=\"mb-0\">Thank you for your compliance, citizen.</p>"
        );
    }

    private function useCovid19Cure(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $inventory = new Inventory($item->getLocationId(), $itemRepo->findAtLocation($item->getLocationId()));

        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            if ($inventoryItem->getId()->equals($item->getId())) {
                $inventoryItem->decrementQuantity();
            }
        }

        /** @var Item $inventoryItem */
        foreach ($inventory->getItems() as $inventoryItem) {
            $itemRepo->save($inventoryItem);
        }

        $player->experienceEvent("selfish-act");
        session()->flash("message", $this->eventRepo->findMessage("selfish-act"));

        $this->playerRepo->save($player);
    }

    private function useTelephone(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $inventory = new Inventory("player", $itemRepo->findAtLocation("player"));

        $number = $request->get("number");

        if (is_null($number)) {
            $message = "<p class=\"mb-0\">That did nothing.</p>";

        } elseif ($number === "999" || $number === "112") {
            $message = "<p class=\"mb-0\">\"You are through to the emergency services. Your call is very important to us. Our lines are busy at the moment. Please call back later if your emergency persists.\"</p>";

        } elseif ($number === "911") {
            $message = "<p class=\"mb-0\">\"This is an automated message from your local police department. If you require police assistance, a SWAT team will be dispatched to your residence. If you require medical assistance, please contact your insurer. If you do not have medical insurance, a SWAT team will be dispatched to your residence.\"</p>";

        } elseif ($number === "08002684319") {
            if ($inventory->hasItemType("covid-19-cure")) {
                $player->experienceEvent("the-right-call");
                $message = $this->eventRepo->findMessage("the-right-call");
            } else {
                $message = "<p>You get through to the quarantine hotline. After several hours of waiting a human finally answers your call. You cannot remember the query you wanted to make and sheepishly hang up without saying anything.</p>"
                    . "<p class=\"mb-0\">Maybe write it down next time?</p>";
            }

        } else {
            $message = "<p class=\"mb-0\">You hear some strange and unfamiliar tones. Was that an international number?</p>";
        }

        session()->flash("messageRaw", $message);

        $this->playerRepo->save($player);
    }

    private function useBed(Request $request, Player $player, Item $item, ItemRepository $itemRepo): void
    {
        if ($player->experiencedEvent("the-right-call")
            && !$player->experiencedEvent("prank-call")
        ) {
            $letterBoxInventory = new Inventory("letter-box", $itemRepo->findAtLocation("letter-box"));

            if ($letterBoxInventory->hasItemType("covid-19-cure")) {
                $player->experienceEvent("save-the-world");
                $player->win();
                $this->playerRepo->save($player);

                session()->flash("messageRaw", $this->eventRepo->findMessage("save-the-world"));
                return;
            }

            $frontGardenInventory = new Inventory("front-garden", $itemRepo->findAtLocation("front-garden"));

            $frontGardenInventory->removeByType("letter-box");

            $item = $itemRepo->createForInventory("dented-letter-box");
            $item->moveTo("front-garden");
            $item->incrementQuantity();
            $frontGardenInventory->add($item);

            /** @var Item $inventoryItem */
            foreach ($frontGardenInventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }

            $player->experienceEvent("prank-call");
            $this->playerRepo->save($player);

            session()->flash("messageRaw", $this->eventRepo->findMessage("prank-call"));
            return;
        }

        $use = $item->getUse();

        $this->playerRepo->save($player);

        session()->flash("success", $use->getMessage());
    }
}
