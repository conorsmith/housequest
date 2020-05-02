<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Item;
use App\Domain\ItemWhereabouts;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use App\UseCases\UseCommand;
use App\ViewModels\AchievementFactory;
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

    /** @var AchievementFactory */
    private $achievementViewModelFactory;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepositoryDb $playerRepo,
        EventFactory $eventViewModelFactory,
        ItemFactory $itemViewModelFactory,
        AchievementFactory $achievementViewModelFactory
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->eventViewModelFactory = $eventViewModelFactory;
        $this->itemViewModelFactory = $itemViewModelFactory;
        $this->achievementViewModelFactory = $achievementViewModelFactory;
    }

    public function __invoke(Request $request, string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        if ($itemId === "00000000-0000-0000-0000-000000000000") {
            session()->flash("info", "You cannot use yourself.");
            return redirect("/{$gameId}");
        }

        $item = $itemRepo->find(Uuid::fromString($itemId));

        if ($this->hasCustomUse($item)) {
            $this->executeCustomUse($request, $item);

            $player = $this->playerRepo->find(Uuid::fromString($gameId));
            $player->useItem($item);
            $achievementIds = $player->unlockAchievements();
            $this->playerRepo->save($player);

            $achievementSessionData = [];

            foreach ($achievementIds as $achievementId) {
                $achievementSessionData[] = $this->achievementViewModelFactory->create($achievementId);
            }

            if (count($achievementSessionData) > 0) {
                session()->flash("achievements", $achievementSessionData);
            }

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
                && !$item->getWhereabouts()->isLocation($player->getLocationId())
            ) {
                session()->flash("info", "You cannot use {$viewModel->label} from your inventory.");
                return redirect("/{$gameId}");
            }

            if ($use->fromInventory()
                && !$item->getWhereabouts()->isPlayer()
            ) {
                session()->flash("info", "You can only use {$viewModel->label} from your inventory.");
                return redirect("/{$gameId}");
            }
        }

        if ($item->isExhaustible()) {
            $inventory = $itemRepo->findInventory($item->getWhereabouts());

            $inventory->removeExpendedItem($item->getId());

            /** @var Item $inventoryItem */
            foreach ($inventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }
        }

        $player->useItem($item);
        $achievementIds = $player->unlockAchievements();

        $this->playerRepo->save($player);

        $achievementSessionData = [];

        foreach ($achievementIds as $achievementId) {
            $achievementSessionData[] = $this->achievementViewModelFactory->create($achievementId);
        }

        if (count($achievementSessionData) > 0) {
            session()->flash("achievements", $achievementSessionData);
        }

        session()->flash("success", $use->getMessage());
        return redirect("/{$gameId}");
    }

    private const CUSTOM_USES = [
        'step-ladder'                 => "useStepLadder",
        'quarantine-extension-notice' => "useQuarantineExtensionNotice",
        'covid-19-cure'               => "useCovid19Cure",
        'telephone'                   => "useTelephone",
        'bed'                         => "useBed",
        'flashlight'                  => "useOnOffItem",
        'quarantine-barrier'          => "useQuarantineBarrier",
        'pager'                       => "usePager",
        'table-lamp'                  => "useOnOffItem",
        'television'                  => "useOnOffItem",
        'alarm-clock'                 => "useOnOffItem",
        'tv-remote'                   => "useTvRemote",
        'sleeping-pills'              => "useBed",
        'hand-towel'                  => "useHandTowel",
        'shower'                      => "useShower",
        'bath-towel'                  => "useBathTowel",
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

            $item->moveTo(ItemWhereabouts::location($player->getLocationId()));

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

        $inventory = $itemRepo->findInventory($item->getWhereabouts());

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

        $inventory = $itemRepo->findInventory(ItemWhereabouts::player());

        $number = $command->getAdditionalData()['number'];

        if (is_null($number)) {
            $message = "<p class=\"mb-0\">That did nothing.</p>";

        } elseif ($number === "999" || $number === "112") {
            $message = "<p class=\"mb-0\">\"You are through to the emergency services. Your call is very important to us. Our lines are busy at the moment. Please call back later if your emergency persists.\"</p>";

        } elseif ($number === "911") {
            $message = "<p class=\"mb-0\">\"This is an automated message from your local police department. If you require police assistance, a SWAT team will be dispatched to your residence. If you require medical assistance, please contact your insurer. If you do not have medical insurance, a SWAT team will be dispatched to your residence.\"</p>";

        } elseif ($number === "08002684319") {
            if ($player->experiencedEvent("prank-call")) {
                $message = "<p>You get through to the quarantine hotline. After hours of waiting a human answers.</p>"
                . "<p>\"Hell– {$player->getName()}? The prank caller? Go fuck yourself!\"</p>"
                . "<p class=\"mb-0\">The call ends. This is why we follow instructions carefully.</p>";
            } elseif ($player->experiencedEvent("beam-in-first-glance")
                || $player->experiencedEvent("beam-in-second-glance")
                || $player->experiencedEvent("beam-in-first-glance-sandwich-retrieval")
                || $player->experiencedEvent("beam-in-second-glance-sandwich-retrieval")
            ) {
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

        if ($item->isExhaustible()) {
            $inventory = $itemRepo->findInventory($item->getWhereabouts());

            $inventory->removeExpendedItem($item->getId());

            /** @var Item $inventoryItem */
            foreach ($inventory->getItems() as $inventoryItem) {
                $itemRepo->save($inventoryItem);
            }
        }

        $allItems = $itemRepo->all();

        /** @var Item $potentiallyResetItem */
        foreach ($allItems as $potentiallyResetItem) {
            if ($potentiallyResetItem->getState() === "wet") {
                $inventory = $itemRepo->findInventory($potentiallyResetItem->getWhereabouts());
                for ($i = 0; $i < $potentiallyResetItem->getQuantity(); $i++) {
                    $inventory->transitionStateOfItem($inventory->find($potentiallyResetItem->getId()), "dry");
                }
                /** @var Item $inventoryItem */
                foreach ($inventory->getItems() as $inventoryItem) {
                    $itemRepo->save($inventoryItem);
                }
            }
        }

        if ($player->experiencedEvent("the-right-call")
            && !$player->experiencedEvent("prank-call")
        ) {
            $frontGardenInventory = $itemRepo->findInventory(ItemWhereabouts::location("front-garden"));

            if ($frontGardenInventory->hasItemType("letter-box")) {

                /** @var Item $item */
                foreach ($frontGardenInventory->getItems() as $item) {
                    if ($item->getTypeId() === "letter-box") {
                        $letterBoxInventory = $itemRepo->findInventory(ItemWhereabouts::itemContents($item->getId()->toString()));
                    }
                }

                if ($letterBoxInventory->hasItemType("covid-19-cure")) {
                    $player->experienceEvent("save-the-world");
                    $player->win();
                    $this->playerRepo->save($player);

                    session()->flash("messageRaw", $this->eventViewModelFactory->createMessage("save-the-world"));
                    return;
                }

                $frontGardenInventory->removeByType("letter-box");

                $item = $itemRepo->createType("dented-letter-box");
                $item->moveTo(ItemWhereabouts::location("front-garden"));
                $item->incrementQuantity();
                $frontGardenInventory->add($item);

                /** @var Item $inventoryItem */
                foreach ($frontGardenInventory->getItems() as $inventoryItem) {
                    $itemRepo->save($inventoryItem);
                }
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

    private function useOnOffItem(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $item = $itemRepo->find($command->getItemId());
        $viewModel = $this->itemViewModelFactory->create($item);
        $useMessages = $this->itemViewModelFactory->createUseMessages($item);

        if ($item->getState() === "on") {
            $item->transitionState("off");
            session()->flash(
                "success",
                array_key_exists($item->getState(), $useMessages->states)
                    ? $useMessages->states[$item->getState()]
                    : "You turned off the {$viewModel->label}."
            );

        } elseif ($item->getState() === "off") {

            if ($item->isPluggable()) {
                $rootWhereabouts = $itemRepo->findRootWhereabouts($item);
                if ($rootWhereabouts->isPlayer()) {
                    session()->flash(
                        "success",
                        "You cannot turn on the {$viewModel->label} while you're holding it."
                    );
                    return;
                }
            }

            $item->transitionState("on");
            session()->flash(
                "success",
                array_key_exists($item->getState(), $useMessages->states)
                    ? $useMessages->states[$item->getState()]
                    : "You turned on the {$viewModel->label}."
            );
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

    private function usePager(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $item = $itemRepo->find($command->getItemId());
        $viewModel = $this->itemViewModelFactory->create($item);

        if ($item->getState() === "on") {
            $item->transitionState("off");
            session()->flash("success", "You turned off the {$viewModel->label}.");

        } elseif ($item->getState() === "off") {
            $item->transitionState("on");

            if ($player->getLocationId() === "front-garden"
                || $player->getLocationId() === "back-garden"
            ) {
                if ($player->experiencedEvent("page-inside")) {
                    $event = $player->experienceEvent("page-outside-try-again");
                } else {
                    $event = $player->experienceEvent("page-outside-first-try");
                }
            } else {
                $event = $player->experienceEvent("page-inside");
            }

            if (is_null($event)) {
                session()->flash("success", "You turned on the {$viewModel->label}.");
            } else {
                session()->flash("messageRaw", $this->eventViewModelFactory->create($event)->message);
            }
        }

        $this->playerRepo->save($player);
        $itemRepo->save($item);
    }

    private function useTvRemote(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $tvRemote = $itemRepo->find($command->getItemId());

        $tvRemoteRootWhereabouts = $itemRepo->findRootWhereabouts($tvRemote);
        if (!($tvRemoteRootWhereabouts->isPlayer() && $player->getLocationId() === "living-room")
            && !$tvRemoteRootWhereabouts->isLocation("living-room")
        ) {
            session()->flash("info", "That did nothing.");
            return;
        }

        $livingRoom = $itemRepo->findInventory(ItemWhereabouts::location("living-room"));

        /** @var Item $item */
        foreach ($livingRoom->getItems() as $item) {
            if ($item->getTypeId() === "television") {
                $television = $item;
            }
        }

        if (!isset($television)) {
            session()->flash("info", "That did nothing.");
            return;
        }

        $televisionViewModel = $this->itemViewModelFactory->create($television);

        if ($television->getState() === "on") {
            $television->transitionState("off");
            session()->flash(
                "success",
                "You turned off the {$televisionViewModel->label} using the remote."
            );

        } elseif ($television->getState() === "off") {
            $television->transitionState("on");
            session()->flash(
                "success",
                "You turned on the {$televisionViewModel->label} using the remote."
            );
        }

        $itemRepo->save($television);
    }

    public function useHandTowel(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $handTowel = $itemRepo->find($command->getItemId());

        if ($handTowel->getState() === "wet") {
            session()->flash("info", "You fail to dry yourself with a wet Hand Towel.");
            return;
        }

        $handsWet = false;
        $faceWet = false;

        /** @var string $condition */
        foreach ($player->getConditions() as $condition) {
            if ($condition === "wet-hands") {
                $handsWet = true;
                $player->removeCondition($condition);
            } elseif ($condition === "wet-face") {
                $faceWet = true;
                $player->removeCondition($condition);
            }
        }

        if (!$handsWet && !$faceWet) {
            session()->flash(
                "success",
                "You rub your dry hands on the Hand Towel."
            );
            return;
        }

        $handTowel->transitionState("wet");

        $itemRepo->save($handTowel);
        $this->playerRepo->save($player);

        if ($handsWet && $faceWet) {
            session()->flash(
                "success",
                "You dry your hands and face."
            );
        } elseif ($handsWet) {
            session()->flash(
                "success",
                "You dry your hands."
            );
        } elseif ($faceWet) {
            session()->flash(
                "success",
                "You dry your face."
            );
        }
    }

    public function useShower(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $shower = $itemRepo->find($command->getItemId());

        $player->addCondition("wet-body");

        $this->playerRepo->save($player);

        session()->flash(
            "success",
            $shower->getUse()->getMessage()
        );
    }

    public function useBathTowel(UseCommand $command): void
    {
        $itemRepo = $this->itemRepoFactory->create($command->getGameId());
        $player = $this->playerRepo->find($command->getGameId());
        $bathTowel = $itemRepo->find($command->getItemId());

        if ($bathTowel->getState() === "wet") {
            session()->flash("info", "You fail to dry yourself with a wet Bath Towel.");
            return;
        }

        $isWet = false;

        /** @var string $condition */
        foreach ($player->getConditions() as $condition) {
            if ($condition === "wet-body") {
                $isWet = true;
                $player->removeCondition($condition);
            }
        }

        if (!$isWet) {
            session()->flash(
                "success",
                "You rub the Bath Towel over your dry body."
            );
            return;
        }

        $inventory = $itemRepo->findInventory($bathTowel->getWhereabouts());
        $inventory->transitionStateOfItem($inventory->find($bathTowel->getId()), "wet");

        /** @var Item $item */
        foreach ($inventory->getItems() as $item) {
            $itemRepo->save($item);
        }

        $this->playerRepo->save($player);

        session()->flash(
            "success",
            "You dry your bod'."
        );
    }
}
