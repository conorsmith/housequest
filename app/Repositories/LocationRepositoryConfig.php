<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Location;
use App\Domain\Player;

final class LocationRepositoryConfig
{
    /** @var array */
    private $config;

    /** @var array */
    private $locations;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->locations = [];

        foreach ($config as $id => $locationConfig) {
            $this->locations[$id] = new Location(
                $id,
                $locationConfig['egresses']
            );
        }
    }

    public function find(string $id): ?Location
    {
        if (!array_key_exists($id, $this->locations)) {
            return null;
        }

        return $this->locations[$id];
    }

    public function findForPlayer(Player $player): ?Location
    {
        return $this->find($player->getLocationId());
    }
}
