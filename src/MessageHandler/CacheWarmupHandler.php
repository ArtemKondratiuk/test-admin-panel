<?php

namespace App\MessageHandler;

use App\Message\CacheWarmupMessage;
use App\Service\ItemServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CacheWarmupHandler
{
    public function __construct(
        private ItemServiceInterface $itemService
    ) {
    }

    public function __invoke(CacheWarmupMessage $message): void
    {
        $this->itemService->getItems($message->getPage(), $message->getLimit());
        $this->itemService->getTotalCount();
    }
}
