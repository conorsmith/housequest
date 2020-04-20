<?php
declare(strict_types=1);

namespace App\Domain;

final class ItemWhereabouts
{
    public static function player(): self
    {
        return new self("player");
    }

    public static function location(string $locationId): self
    {
        return new self($locationId);
    }

    public static function itemContents(string $locationId): self
    {
        return new self($locationId);
    }

    /** @var string */
    private $locationId;

    public function __construct(string $locationId)
    {
        $this->locationId = $locationId;
    }

    public function isPlayer(): bool
    {
        return $this->locationId === "player";
    }

    public function isLocation(string $locationId): bool
    {
        return $this->locationId === $locationId;
    }

    public function __toString(): string
    {
        return $this->locationId;
    }
}
