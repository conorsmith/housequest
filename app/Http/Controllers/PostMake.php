<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
use App\Domain\Player;
use App\Domain\Recipe;
use App\Domain\RecipeIngredient;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepositoryDb;
use App\Repositories\RecipeRepositoryConfig;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class PostMake extends Controller
{
    /** @var ItemRepositoryDb */
    private $itemRepoFactory;

    /** @var PlayerRepositoryDb */
    private $playerRepo;

    private $recipeRepo;

    public function __construct(
        ItemRepositoryDbFactory $itemRepoFactory,
        PlayerRepositoryDb $playerRepo,
        RecipeRepositoryConfig $recipeRepo
    ) {
        $this->itemRepoFactory = $itemRepoFactory;
        $this->playerRepo = $playerRepo;
        $this->recipeRepo = $recipeRepo;
    }

    public function __invoke(Request $request, string $gameId)
    {
        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        if ($player->isDead()) {
            session()->flash("info", "You cannot do that, you're dead.");
            return redirect("/{$gameId}");
        }

        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $recipe = $this->findRecipeForRequest($request, $player);

        if (is_null($recipe)) {
            session()->flash("info", "You failed to make anything.");
            return redirect("/{$gameId}");
        }

        $inventories = $this->removeUsedItemsFromInventory($request, $itemRepo, $player);

        $endProduct = $this->getEndProduct($itemRepo, $recipe);
        $endProduct->addQuantity($recipe->getEndProductQuantity());
        if ($recipe->getEndProductionLocationId() === "room") {
            $endProduct->moveTo($player->getLocationId());
        }

        /** @var Inventory $inventory */
        foreach ($inventories as $inventory) {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                $itemRepo->save($item);
            }
        }

        $itemRepo->save($endProduct);
        $this->playerRepo->save($player);

        if ($recipe->getEndProductQuantity() > 1) {
            $output = "{$endProduct->getName()} ({$recipe->getEndProductQuantity()})";
        } else {
            $output = $endProduct->getName();
        }

        session()->flash("success", "You made {$output}.");
        return redirect("/{$gameId}");
    }

    private function findRecipeForRequest(Request $request, Player $player): ?Recipe
    {
        $itemRepo = $this->itemRepoFactory->create(
            Uuid::fromString($request->route()->parameter("gameId"))
        );

        $submittedItemsQuantitiesById = $this->getSubmittedItemQuantitiesById($request);
        $submittedItemsPortionsById = $this->getSubmittedItemPortionsById($request);

        $inventories = [
            $itemRepo->findInventory("player"),
            $itemRepo->findInventory($player->getLocationId()),
        ];

        $submittedIngredients = [];

        foreach ($inventories as $inventory) {
            /** @var Item $inventoryItem */
            foreach ($inventory->getItems() as $inventoryItem) {
                if (array_key_exists($inventoryItem->getId()->toString(), $submittedItemsQuantitiesById)) {
                    $submittedIngredients[] = new RecipeIngredient(
                        $inventoryItem->getTypeId(),
                        intval($submittedItemsQuantitiesById[$inventoryItem->getId()->toString()]),
                        0
                    );
                }
                if (array_key_exists($inventoryItem->getId()->toString(), $submittedItemsPortionsById)) {
                    $submittedIngredients[] = new RecipeIngredient(
                        $inventoryItem->getTypeId(),
                        0,
                        intval($submittedItemsPortionsById[$inventoryItem->getId()->toString()])
                    );
                }
            }
        }

        return $this->recipeRepo->findForIngredients($submittedIngredients);
    }

    private function removeUsedItemsFromInventory(Request $request, ItemRepositoryDb $itemRepo, Player $player): array
    {
        $inventories = [
            $itemRepo->findInventory("player"),
            $itemRepo->findInventory($player->getLocationId()),
        ];

        $submittedItemsQuantitiesById = $this->getSubmittedItemQuantitiesById($request);

        /** @var Inventory $inventory */
        foreach ($inventories as $inventory) {
            foreach ($submittedItemsQuantitiesById as $id => $quantity) {
                $item = $inventory->find(Uuid::fromString($id));
                if (!is_null($item)) {
                    $item->removeQuantity(intval($quantity));
                }
            }
        }

        $submittedItemsPortionsById = $this->getSubmittedItemPortionsById($request);

        /** @var Inventory $inventory */
        foreach ($inventories as $inventory) {
            foreach ($submittedItemsPortionsById as $id => $portions) {
                $item = $inventory->find(Uuid::fromString($id));
                if (!is_null($item)) {
                    $inventory->removePortionsFromItem($item, intval($portions));
                }
            }
        }

        return $inventories;
    }

    private function getEndProduct(ItemRepositoryDb $itemRepo, Recipe $recipe): Item
    {
        $inventoryItems = $itemRepo->findInventory("player")->getItems();

        /** @var Item $item */
        foreach ($inventoryItems as $item) {
            if ($item->getTypeId() === $recipe->getEndProduct()) {
                return $item;
            }
        }

        return $itemRepo->createForInventory($recipe->getEndProduct());
    }

    private function getSubmittedItemQuantitiesById(Request $request): array
    {
        $submittedItemsQuantitiesById = $request->input("itemQuantities", []);

        foreach ($submittedItemsQuantitiesById as $id => $quantity) {
            if ($quantity === "0") {
                unset($submittedItemsQuantitiesById[$id]);
            }
        }

        return $submittedItemsQuantitiesById;
    }

    private function getSubmittedItemPortionsById(Request $request): array
    {
        $submittedItemsPortionsById = $request->input("itemPortions", []);

        foreach ($submittedItemsPortionsById as $id => $portions) {
            if ($portions === "0") {
                unset($submittedItemsPortionsById[$id]);
            }
        }

        return $submittedItemsPortionsById;
    }
}
