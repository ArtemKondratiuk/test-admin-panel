<?php

namespace App\Message;

use App\Service\ItemServiceInterface;

class CacheWarmupMessage
{
    public function __construct(
        private int $page = 1,
        private int $limit = ItemServiceInterface::ITEMS_PER_PAGE
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
