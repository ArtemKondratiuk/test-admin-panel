<?php

namespace App\Service;

use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Entity\Item;

interface ItemServiceInterface
{
    public const ITEMS_PER_PAGE = 3;

    /**
     * @param Item[] $items
     * @return PaginationInterface<int, Item>
     */
    public function createPagination(array $items, int $totalCount, int $page): PaginationInterface;
    /**
     * @return PaginationInterface<int, Item>
    */
    public function getPaginatedItems(int $page): PaginationInterface;
    /** @return Item[] */
    public function getItems(int $page, int $limit): array;
    public function getTotalCount(): int;
    public function createItem(Item $item): void;
    public function updateItem(Item $item): void;
    public function deleteItem(Item $item): void;
    public function moveItem(Item $item, string $direction): void;
}
