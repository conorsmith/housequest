<?php
declare(strict_types=1);

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;

final class Player
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $locationId;

    /** @var int */
    private $xp;

    public function __construct(UuidInterface $id, string $locationId, int $xp)
    {
        $this->id = $id;
        $this->locationId = $locationId;
        $this->xp = $xp;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function move(string $locationId): void
    {
        $this->locationId = $locationId;
    }

    public function gainXp(int $gain): void
    {
        $this->xp += $gain;
    }
}
