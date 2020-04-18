<?php
declare(strict_types=1);

namespace App\ViewModels;

use stdClass;

final class AchievementFactory
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = [];

        foreach ($config as $achievement) {
            $this->config[$achievement['id']] = $achievement;
        }
    }

    public function create(string $achievementId): stdClass
    {
        $achievementConfig = $this->config[$achievementId];

        return (object) [
            'title' => $achievementConfig['title'],
            'body'  => $achievementConfig['body'],
        ];
    }
}
