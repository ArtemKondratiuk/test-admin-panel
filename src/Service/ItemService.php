<?php

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ItemService implements ItemServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ItemRepository $repository,
        private PaginatorInterface $paginator
    ) {
    }

    /**
     * @return Item[]
     */
    public function getItems(int $page, int $limit): array
    {
        return $this->repository->findBy(
            [],
            ['position' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );
    }

    /**
     * @return PaginationInterface<int, Item>
     */
    public function getPaginatedItems(int $page): PaginationInterface
    {
        $limit = ItemServiceInterface::ITEMS_PER_PAGE;

        return $this->createPagination(
            $this->getItems($page, $limit),
            $this->getTotalCount(),
            $page
        );
    }

    /**
     * @param Item[] $items
     * @return PaginationInterface<int, Item>
     */
    public function createPagination(array $items, int $totalCount, int $page): PaginationInterface
    {
        $limit = ItemServiceInterface::ITEMS_PER_PAGE;
        $pagination = $this->paginator->paginate($items, 1, $limit);
        $pagination->setTotalItemCount($totalCount);
        $pagination->setCurrentPageNumber($page);

        return $pagination;
    }

    public function createItem(Item $item): void
    {
        if (null === $item->getId()) {
            $this->repository->shiftPositions();
            $item->setPosition(1);
        }

        $this->em->persist($item);
        $this->em->flush();
        $this->repository->reindexAllPositions();
    }

    public function updateItem(Item $item): void
    {
        $this->em->flush();
        $this->repository->reindexAllPositions();
    }

    public function deleteItem(Item $item): void
    {
        $this->em->remove($item);
        $this->em->flush();
        $this->repository->reindexAllPositions();
    }

    public function moveItem(Item $item, string $direction): void
    {
        $oldPos = (int)$item->getPosition();
        $newPos = ($direction === 'up') ? $oldPos - 1 : $oldPos + 1;

        if ($newPos < 1 || $newPos > $this->getTotalCount()) {
              return;
        }

        $this->repository->updatePositionAndShiftOthers($item, $newPos);
    }


    public function getTotalCount(): int
    {
        return $this->repository->count([]);
    }
}
