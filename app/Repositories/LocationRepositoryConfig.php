<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Player;

final class LocationRepositoryConfig
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function findForPlayer(Player $player)
    {
        return $this->config[$player->getLocationId()];
    }
}
