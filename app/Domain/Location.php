<?php
declare(strict_types=1);

namespace App\Domain;

final class Location
{
    /** @var string */
    private $id;

    /** @var array */
    private $egresses;

    public function __construct(string $id, array $egresses)
    {
        $this->id = $id;
        $this->egresses = $egresses;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEgresses(Inventory $inventory): array
    {
        $egresses = $this->egresses;

        if ($this->id === "landing") {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                if (($item->getTypeId() === "step-ladder" && $item->getState() === "open")
                    || $item->getTypeId() === "chair-ladder"
                ) {
                    $egresses[] = "attic";
                }
            }
        } elseif ($this->id === "front-garden") {
            /** @var Item $item */
            foreach ($inventory->getItems() as $item) {
                if ($item->getTypeId() === "quarantine-barrier" && $item->getState() === "open") {
                    array_unshift($egresses, "the-street");
                }
            }

        }

        return array_unique($egresses);
    }
}
