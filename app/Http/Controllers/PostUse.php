<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use Illuminate\Validation\Rules\In;
use Ramsey\Uuid\Uuid;

final class PostUse extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    public function __construct(ItemRepositoryDbFactory $itemRepoFactory, PlayerRepositoryDb $playerRepo)
    {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
    }

    public function __invoke(string $gameId, string $itemId)
    {
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $item = $itemRepo->find($itemId);

        if ($this->hasCustomUse($item)) {
            $this->executeCustomUse($player, $item, $itemRepo);
            return redirect("/{$gameId}");
        }

        if (!$item->hasUse()) {
            session()->flash("info", "That did nothing.");
            return redirect("/{$gameId}");
        }

        $use = $item->getUse();

        $player->gainXp($use->getXp());

        $this->playerRepo->save($player);

        session()->flash("success", "{$use->getMessage()} You gained {$use->getXp()} XP.");
        return redirect("/{$gameId}");
    }

    private const CUSTOM_USES = [
        'step-ladder'                 => "useStepLadder",
        'deployed-step-ladder'        => "useDeployedStepLadder",
        'quarantine-extension-notice' => "useQuarantineExtensionNotice",
        'covid-19-cure'               => "useCovid19Cure",
    ];

    private function hasCustomUse(Item $item): bool
    {
        return array_key_exists($item->getTypeId(), self::CUSTOM_USES);
    }

    private function executeCustomUse(Player $player, Item $item, ItemRepository $itemRepo): void
    {
        $customUseFunction = self::CUSTOM_USES[$item->getTypeId()];
        $this->$customUseFunction($player, $item, $itemRepo);
    }

    private function useStepLadder(Player $player, Item $item, ItemRepository $itemRepo): void
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

    private function useDeployedStepLadder(Player $player, Item $item, ItemRepository $itemRepo): void
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

    private function useQuarantineExtensionNotice(Player $player, Item $item, ItemRepository $itemRepo): void
    {
        session()->flash(
            "infoRaw",
            "<p><strong>QUARANTINE EXTENSION NOTICE</strong></p>"
            . "<p>The existing quarantine protocol is being extended by another month. Please remain in your home at all times.</p>"
            . "<p>Failure to comply with the quarantine protocol will result in a fine not exceeding €1,000 and/or immediate execution.</p>"
            . "<p class=\"mb-0\">Thank you for your compliance, citizen.</p>"
        );
    }

    private function useCovid19Cure(Player $player, Item $item, ItemRepository $itemRepo): void
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

        session()->flash("info", "You inject yourself with the cure for Covid-19 without handing it over to scientists for study. You are now immune to the virus and everybody else is still fucked. Well done.");
    }
}
