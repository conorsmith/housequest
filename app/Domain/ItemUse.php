<?php
declare(strict_types=1);

namespace App\Domain;

final class ItemUse
{
    /** @var array */
    private $from;

    /** @var string */
    private $message;

    /** @var int */
    private $xp;

    public function __construct(array $from, string $message, int $xp)
    {
        $this->from = $from;
        $this->message = $message;
        $this->xp = $xp;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function fromRoom(): bool
    {
        return in_array("room", $this->from);
    }

    public function fromInventory(): bool
    {
        return in_array("inventory", $this->from);
    }
}
