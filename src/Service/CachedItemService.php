<?php

namespace App\Service;

use App\Entity\Item;
use App\Message\CacheWarmupMessage;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(ItemServiceInterface::class)]
class CachedItemService implements ItemServiceInterface
{
    private const CACHE_TAG = 'items_list';

    public function __construct(
        #[Autowire(service: ItemService::class)]
        private ItemServiceInterface $itemService,
        #[Autowire(service: 'items.cache')]
        private TagAwareCacheInterface $itemsCache,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @return PaginationInterface<int, Item>
     */
    public function getPaginatedItems(int $page): PaginationInterface
    {
        $items = $this->getItems($page, ItemServiceInterface::ITEMS_PER_PAGE);
        $totalCount = $this->getTotalCount();

        return $this->itemService->createPagination($items, $totalCount, $page);
    }

    /**
     * @return Item[]
     */
    public function getItems(int $page, int $limit): array
    {
        return $this->itemsCache->get("items_p{$page}_l{$limit}", function (ItemInterface $item) use ($page, $limit) {
            $item->tag([self::CACHE_TAG]);
            $item->expiresAfter(3600);

            return $this->itemService->getItems($page, $limit);
        });
    }

    public function getTotalCount(): int
    {
        return $this->itemsCache->get('items_total_count', function (ItemInterface $item) {
            $item->tag([self::CACHE_TAG]);

            return $this->itemService->getTotalCount();
        });
    }

    public function createItem(Item $item): void
    {
        $this->itemService->createItem($item);
        $this->refreshCache();
    }

    public function updateItem(Item $item): void
    {
        $this->itemService->updateItem($item);
        $this->refreshCache();
    }

    public function deleteItem(Item $item): void
    {
        $this->itemService->deleteItem($item);
        $this->refreshCache();
    }

    public function moveItem(Item $item, string $direction): void
    {
        $this->itemService->moveItem($item, $direction);
        $this->refreshCache();
    }

    private function refreshCache(): void
    {
        $this->itemsCache->invalidateTags([self::CACHE_TAG]);
        $this->bus->dispatch(new CacheWarmupMessage(1, ItemServiceInterface::ITEMS_PER_PAGE));
    }

    /**
     * @param Item[] $items
     * @return PaginationInterface<int, Item>
    */
    public function createPagination(array $items, int $totalCount, int $page): PaginationInterface
    {
        return $this->itemService->createPagination($items, $totalCount, $page);
    }
}
