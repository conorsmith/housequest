<?php
declare(strict_types=1);

namespace App\Domain;

use Illuminate\Support\Arr;

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

        if (is_array($config['output'])) {
            foreach ($config['output'] as $key => $value) {
                $outputTypeId = $key;
                $outputQuantity = $value['quantity'];
            }
        } else {
            $outputTypeId = $config['output'];
            $outputQuantity = 1;
        }

        $outputLocation = Arr::get($config, 'location', "player");

        return new self($ingredients, $outputTypeId, $outputQuantity, $outputLocation);
    }

    /** @var array */
    private $ingredients;

    /** @var string */
    private $outputItemTypeId;

    /** @var int */
    private $outputItemQuantity;

    /** @var string */
    private $outputLocationId;

    public function __construct(array $ingredients, string $outputItemTypeId, int $outputItemQuantity, string $outputLocationId)
    {
        $this->ingredients = $ingredients;
        $this->outputItemTypeId = $outputItemTypeId;
        $this->outputItemQuantity = $outputItemQuantity;
        $this->outputLocationId = $outputLocationId;
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

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function getEndProduct(): string
    {
        return $this->outputItemTypeId;
    }

    public function getEndProductQuantity(): int
    {
        return $this->outputItemQuantity;
    }

    public function getEndProductionLocationId(): string
    {
        return $this->outputLocationId;
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
