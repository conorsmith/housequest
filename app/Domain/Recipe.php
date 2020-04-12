<?php
declare(strict_types=1);

namespace App\Domain;

final class Recipe
{
    public static function fromConfig(array $config): self
    {
        $ingredients = [];

        foreach ($config['input'] as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('quantity', $value)) {
                    $ingredients[] = new RecipeIngredient($key, $value['quantity'], 0);

                } elseif (array_key_exists('portions', $value)) {
                    $ingredients[] = new RecipeIngredient($key, 0, $value['portions']);

                } else {
                    $ingredients[] = new RecipeIngredient($key, 1, 0);
                }
            } else {
                $ingredients[] = new RecipeIngredient($value, 1, 0);
            }
        }

        return new self($ingredients, $config['output']);
    }

    /** @var array */
    private $ingredients;

    /** @var string */
    private $outputItemTypeId;

    public function __construct(array $ingredients, string $outputItemTypeId)
    {
        $this->ingredients = $ingredients;
        $this->outputItemTypeId = $outputItemTypeId;
    }

    public function toArray(): array
    {
        $input = [];

        /** @var RecipeIngredient $ingredient */
        foreach ($this->ingredients as $ingredient) {
            if ($ingredient->getQuantity() === 1) {
                $input[] = $ingredient->getTypeId();
            } else {
                $input[$ingredient->getTypeId()] = $ingredient->getQuantity();
            }
        }

        return [
            'input'  => $input,
            'output' => $this->outputItemTypeId,
        ];
    }

    public function findIngredient(string $itemTypeId): ?RecipeIngredient
    {
        /** @var RecipeIngredient $ingredient */
        foreach ($this->ingredients as $ingredient) {
            if ($ingredient->getTypeId() === $itemTypeId) {
                return $ingredient;
            }
        }

        return null;
    }

    public function getEndProduct(): string
    {
        return $this->outputItemTypeId;
    }

    public function matches(array $submittedIngredients): bool
    {
        return $this->itemsMatch($submittedIngredients)
            && $this->quantitiesMatch($submittedIngredients)
            && $this->portionsMatch($submittedIngredients);
    }

    private function itemsMatch(array $submittedIngredients): bool
    {
        $ingredientsItemTypeIds = array_map(
            function (RecipeIngredient $recipeIngredient) {
                return $recipeIngredient->getTypeId();
            },
            $this->ingredients
        );
        sort($ingredientsItemTypeIds);

        $submittedIngredientsItemTypeIds = array_map(
            function (RecipeIngredient $recipeIngredient) {
                return $recipeIngredient->getTypeId();
            },
            $submittedIngredients
        );
        sort($submittedIngredientsItemTypeIds);

        return $ingredientsItemTypeIds === $submittedIngredientsItemTypeIds;
    }

    private function quantitiesMatch(array $submittedIngredients): bool
    {
        /** @var RecipeIngredient $submittedIngredient */
        foreach ($submittedIngredients as $submittedIngredient) {
            $requiredIngredient = $this->findIngredient($submittedIngredient->getTypeId());
            if ($requiredIngredient->getQuantity() !== $submittedIngredient->getQuantity()) {
                return false;
            }
        }

        return true;
    }

    private function portionsMatch(array $submittedIngredients): bool
    {
        /** @var RecipeIngredient $submittedIngredient */
        foreach ($submittedIngredients as $submittedIngredient) {
            $requiredIngredient = $this->findIngredient($submittedIngredient->getTypeId());
            if ($requiredIngredient->getPortions() !== $requiredIngredient->getPortions()) {
                return false;
            }
        }

        return true;
    }
}
