<?php
declare(strict_types=1);

namespace App\Domain;

final class ItemUse
{
    /** @var array */
    private $from;

    /** @var string */
    private $message;

    public function __construct(array $from, string $message)
    {
        $this->from = $from;
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function hasRestrictions(): bool
    {
        return count($this->from) > 0;
    }

    public function fromRoom(): bool
    {
        return $this->hasRestrictions() && in_array("room", $this->from);
    }

    public function fromInventory(): bool
    {
        return $this->hasRestrictions() && in_array("inventory", $this->from);
    }
}
