<?php
declare(strict_types=1);

namespace App\Repositories;

final class AchievementRepositoryConfig
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function find(string $id): array
    {
        foreach ($this->config as $achievementConfig) {
            if ($achievementConfig['id'] === $id) {
                return $achievementConfig;
            }
        }
    }
}
