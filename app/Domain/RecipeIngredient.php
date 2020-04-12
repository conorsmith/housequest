<?php
declare(strict_types=1);

namespace App\Domain;

final class RecipeIngredient
{
    /** @var string */
    private $typeId;

    /** @var int */
    private $quantity;

    /** @var int */
    private $portions;

    public function __construct(string $typeId, int $quantity, int $portions)
    {
        $this->typeId = $typeId;
        $this->quantity = $quantity;
        $this->portions = $portions;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPortions(): int
    {
        return $this->portions;
    }
}
