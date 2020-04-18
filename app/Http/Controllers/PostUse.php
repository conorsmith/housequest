<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use App\UseCases\UseCommand;
use App\ViewModels\EventFactory;
use App\ViewModels\ItemFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostUse extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    /** @var EventFactory */
    private $eventViewModelFactory;

    /** @var ItemFactory */
    private $itemViewModelFactory;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepositoryDb $playerRepo,
        EventFactory $eventViewModelFactory,
        ItemFactory $itemViewModelFactory
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->eventViewModelFactory = $eventViewModelFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
    }

    public function __invoke(Request $request, string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $item = $itemRepo->find(Uuid::fromString($itemId));

        if ($this->hasCustomUse($item)) {
            $this->executeCustomUse($request, $item);
            return redirect("/{$gameId}");
        }

        if (!$item->hasUse()) {
            session()->flash("info", "That did nothing.");
            return redirect("/{$gameId}");
        }

        $viewModel = $this->itemViewModelFactory->create($item);

        $use = $item->getUse();

        if ($use->hasRestrictions()) {

            if ($use->fromRoom()
                && $item->getLocationId() !== $player->getLocationId()
            ) {
                session()->flash("info", "You cannot use {$viewModel->label} from your inventory.");
                return redirect("/{$gameId}");
            }

            if ($use->fromInventory()
                && $item->getLocationId() !== "player"
            ) {
                session()->flash("info", "You can only use {$viewModel->label} from your inventory.");
                return redirect("/{$gameId}");
            }
        }

        $this->playerRepo->save($player);

        session()->flash("success", $use->getMessage());
        return redirect("/{$gameId}");
    }

    private const CUSTOM_USES = [
        'step-ladder'                 => "useStepLadder",
        'quarantine-extension-notice' => "useQuarantineExtensionNotice",
        'covid-19-cure'               => "useCovid19Cure",
        'telephone'                   => "useTelephone",
        'bed'                         => "useBed",
        'flashlight'                  => "useFlashlight",
        'quarantine-barrier'          => "useQuarantineBarrier",
    ];

    private function hasCustomUse(Item $item): bool
    {
        return array_key_exists($item->getTypeId(), self::CUSTOM_USES);
    }

    private function executeCustomUse(Request $request, Item $item): void
    {
        $customUseFunction = self::CUSTOM_USES[$item->getTypeId()];
        $this->$customUseFunction(new UseCommand(
            Uuid::fromString($request->route("gameId")),
            Uuid::fromString($request->route("itemId")),
            $request->input()
        ));
    }

    private function useStepLadder(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $item = $itemRepo->find($command->getItemId());
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getState() === "closed") {
            $item->transitionState("open");

            $item->moveTo($player->getLocationId());

            session()->flash("success", "You deployed the {$viewModel->label}.");

        } elseif ($item->getState() === "open") {
            $item->transitionState("closed");
            session()->flash("success", "You closed the {$viewModel->label}.");
        }

        $itemRepo->save($item);
    }

    private function useQuarantineExtensionNotice(UseCommand $command): void
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

    private function useCovid19Cure(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $item = $itemRepo->find($command->getItemId());

        $inventory = $itemRepo->findInventory($item->getLocationId());

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
        session()->flash("message", $this->eventViewModelFactory->createMessage("selfish-act"));

        $this->playerRepo->save($player);
    }

    private function useTelephone(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());

        $inventory = $itemRepo->findInventory("player");

        $number = $command->getAdditionalData()['number'];

        if (is_null($number)) {
            $message = "<p class=\"mb-0\">That did nothing.</p>";

        } elseif ($number === "999" || $number === "112") {
            $message = "<p class=\"mb-0\">\"You are through to the emergency services. Your call is very important to us. Our lines are busy at the moment. Please call back later if your emergency persists.\"</p>";

        } elseif ($number === "911") {
            $message = "<p class=\"mb-0\">\"This is an automated message from your local police department. If you require police assistance, a SWAT team will be dispatched to your residence. If you require medical assistance, please contact your insurer. If you do not have medical insurance, a SWAT team will be dispatched to your residence.\"</p>";

        } elseif ($number === "08002684319") {
            if ($inventory->hasItemType("covid-19-cure")) {
                $player->experienceEvent("the-right-call");
                $message = $this->eventViewModelFactory->createMessage("the-right-call");
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

    private function useBed(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $item = $itemRepo->find($command->getItemId());

        if ($player->experiencedEvent("the-right-call")
            && !$player->experiencedEvent("prank-call")
        ) {
            $letterBoxInventory = $itemRepo->findInventory("letter-box");

            if ($letterBoxInventory->hasItemType("covid-19-cure")) {
                $player->experienceEvent("save-the-world");
                $player->win();
                $this->playerRepo->save($player);

                session()->flash("messageRaw", $this->eventViewModelFactory->createMessage("save-the-world"));
                return;
            }

            $frontGardenInventory = $itemRepo->findInventory("front-garden");

            $frontGardenInventory->removeByType("letter-box");

            $item = $itemRepo->createType("dented-letter-box");
            $item->moveTo("front-garden");
            $item->incrementQuantity();
            $frontGardenInventory->add($item);

            /** @var Item $inventoryItem */
            foreach ($frontGardenInventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }

            $player->experienceEvent("prank-call");
            $this->playerRepo->save($player);

            session()->flash("messageRaw", $this->eventViewModelFactory->createMessage("prank-call"));
            return;
        }

        $use = $item->getUse();

        $this->playerRepo->save($player);

        session()->flash("success", $use->getMessage());
    }

    private function useFlashlight(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $item = $itemRepo->find($command->getItemId());
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getState() === "on") {
            $item->transitionState("off");
            session()->flash("success", "You turned off the {$viewModel->label}.");

        } elseif ($item->getState() === "off") {
            $item->transitionState("on");
            session()->flash("success", "You turned on the {$viewModel->label}.");
        }

        $itemRepo->save($item);
    }

    private function useQuarantineBarrier(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $item = $itemRepo->find($command->getItemId());
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getState() === "closed") {
            $item->transitionState("open");
            $event = $player->experienceEvent("no-way-out");
            if (is_null($event)) {
                session()->flash("success", "You opened the {$viewModel->label}.");
            } else {
                session()->flash("messageRaw", $this->eventViewModelFactory->create($event)->message);
            }

        } elseif ($item->getState() === "open") {
            $item->transitionState("closed");
            session()->flash("success", "You closed the {$viewModel->label}.");
        }

        $this->playerRepo->save($player);
        $itemRepo->save($item);
    }
}
