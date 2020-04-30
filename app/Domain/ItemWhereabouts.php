<?php
declare(strict_types=1);

namespace App\Domain;

final class ItemWhereabouts
{
    public static function player(): self
    {
        return new self("player", "player");
    }

    public static function location(string $locationId): self
    {
        return new self($locationId, "location");
    }

    public static function itemContents(string $itemId): self
    {
        return new self($itemId, "item-contents");
    }

    public static function itemSurface(string $itemId): self
    {
        return new self($itemId, "item-surface");
    }

    /** @var string */
    private $id;

    /** @var string */
    private $type;

    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id
            && $this->type === $other->type;
    }

    public function isPlayer(): bool
    {
        return $this->id === "player"
            && $this->type === "player";
    }

    public function isLocation(string $locationId): bool
    {
        return $this->id === $locationId
            && $this->type === "location";
    }

    public function isSomeLocation(): bool
    {
        return $this->type === "location";
    }

    public function isOnSomething(): bool
    {
        return $this->type === "item-surface";
    }

    public function isInSomething(): bool
    {
        return $this->type === "item-contents";
    }

    public function isForItem(Item $item): bool
    {
        return $this->id === $item->getId()->toString()
            && in_array($this->type, ["item-contents", "item-surface"]);
    }
}
