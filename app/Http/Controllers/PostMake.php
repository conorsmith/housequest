<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory;
use App\Domain\Item;
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
        $itemRepo = $this->itemRepoFactory->create(Uuid::fromString($gameId));

        $recipe = $this->findRecipeForRequest($request);

        if (is_null($recipe)) {
            session()->flash("info", "You failed to make anything.");
            return redirect("/{$gameId}");
        }

        $player = $this->playerRepo->find(Uuid::fromString($gameId));

        $inventoryItems = $this->removeUsedItemsFromInventory($request, $itemRepo);

        $endProduct = $this->getEndProduct($itemRepo, $recipe);
        $endProduct->incrementQuantity();

        $player->gainXp(10);

        foreach ($inventoryItems as $item) {
            $itemRepo->save($item);
        }

        $itemRepo->save($endProduct);
        $this->playerRepo->save($player);

        session()->flash("success", "You made {$endProduct->getName()}. You gained 10 XP.");
        return redirect("/{$gameId}");
    }

    private function findRecipeForRequest(Request $request): ?Recipe
    {
        $itemRepo = $this->itemRepoFactory->create(
            Uuid::fromString($request->route()->parameter("gameId"))
        );

        $submittedItemsQuantitiesById = $this->getSubmittedItemQuantitiesById($request);
        $submittedItemsPortionsById = $this->getSubmittedItemPortionsById($request);

        $inventoryItems = $itemRepo->getInventory();

        $submittedIngredients = [];

        /** @var Item $inventoryItem */
        foreach ($inventoryItems as $inventoryItem) {
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

        return $this->recipeRepo->findForIngredients($submittedIngredients);
    }

    private function removeUsedItemsFromInventory(Request $request, ItemRepositoryDb $itemRepo): array
    {
        $inventory = new Inventory("player", $itemRepo->getInventory());

        $submittedItemsQuantitiesById = $this->getSubmittedItemQuantitiesById($request);

        foreach ($submittedItemsQuantitiesById as $id => $quantity) {
            $item = $inventory->find(Uuid::fromString($id));
            $item->removeQuantity(intval($quantity));
        }

        $submittedItemsPortionsById = $this->getSubmittedItemPortionsById($request);

        foreach ($submittedItemsPortionsById as $id => $portions) {
            $item = $inventory->find(Uuid::fromString($id));
            $inventory->removePortionsFromItem($item, intval($portions));
        }

        return $inventory->getItems();
    }

    private function getEndProduct(ItemRepositoryDb $itemRepo, Recipe $recipe): Item
    {
        $inventoryItems = $itemRepo->getInventory();

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
