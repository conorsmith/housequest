<?php
declare(strict_types=1);

namespace App\ViewModels;

use App\Domain\Event;
use App\Repositories\LocationRepositoryConfig;
use stdClass;

final class EventFactory
{
    /** @var array */
    private $config;

    /** @var LocationFactory */
    private $locationViewModelFactory;

    /** @var LocationRepositoryConfig */
    private $locationRepo;

    public function __construct(
        array $config,
        LocationFactory $locationViewModelFactory,
        LocationRepositoryConfig $locationRepo
    ) {
        $this->config = $config;
        $this->locationViewModelFactory = $locationViewModelFactory;
        $this->locationRepo = $locationRepo;
    }

    public function create(Event $event): stdClass
    {
        return (object) [
            'message'  => $this->createMessage($event->getId()),
            'location' => $this->locationViewModelFactory->create(
                $this->locationRepo->find($event->getLocationId())
            ),
        ];
    }

    public function createMessage(string $id): string
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
