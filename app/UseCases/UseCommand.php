<?php
declare(strict_types=1);

namespace App\UseCases;

use Ramsey\Uuid\UuidInterface;

final class UseCommand
{
    /** @var UuidInterface */
    private $gameId;

    /** @var UuidInterface */
    private $itemId;

    /** @var array */
    private $additionalData;

    public function __construct(UuidInterface $gameId, UuidInterface $itemId, array $additionalData)
    {
        $this->gameId = $gameId;
        $this->itemId = $itemId;
        $this->additionalData = $additionalData;
    }

    public function getGameId(): UuidInterface
    {
        return $this->gameId;
    }

    public function getItemId(): UuidInterface
    {
        return $this->itemId;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
