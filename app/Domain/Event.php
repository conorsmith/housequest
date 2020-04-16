<?php
declare(strict_types=1);

namespace App\Domain;

final class Event
{
    /** @var string */
    private $id;

    /** @var string */
    private $locationId;

    public function __construct(string $id, string $locationId)
    {
        $this->id = $id;
        $this->locationId = $locationId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }
}
