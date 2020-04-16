<?php
declare(strict_types=1);

namespace App\Repositories;

final class EventRepositoryConfig
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function findMessage(string $id): string
    {
        $message = $this->config[$id]['message'];

        if (is_string($message)) {
            return $message;
        }

        $lastParagraph = array_pop($message);

        $output = "";

        foreach ($message as $paragraph) {
            $output .= "<p>{$paragraph}</p>";
        }

        $output .= "<p class=\"mb-0\">{$lastParagraph}</p>";

        return $output;
    }
}
