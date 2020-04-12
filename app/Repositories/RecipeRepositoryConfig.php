<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Recipe;

final class RecipeRepositoryConfig
{
    /** @var array */
    private $recipes;

    public function __construct(array $config)
    {
        $this->recipes = [];

        /** @var array $recipeConfig */
        foreach ($config as $recipeConfig) {
            $this->recipes[] = Recipe::fromConfig($recipeConfig);
        }
    }

    public function findForIngredients(array $ingredients): ?Recipe
    {
        /** @var Recipe $recipe */
        foreach ($this->recipes as $recipe) {
            if ($recipe->matches($ingredients)) {
                return $recipe;
            }
        }

        return null;
    }
}
