<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function getNextPosition(): int
    {
        $result = $this->createQueryBuilder('l')
            ->select('MAX(l.position)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$result + 1;
    }

    public function reindexAllPositions(): void
    {
        $em = $this->getEntityManager();
        $query = $this->createQueryBuilder('l')
            ->orderBy('l.position', 'ASC')
            ->getQuery();

        $index = 1;
        foreach ($query->toIterable() as $item) {
            /** @var Item $item */
            if ($item->getPosition() !== $index) {
                $item->setPosition($index);
            }
            $index++;

            if (($index % 50) === 0) {
                $em->flush();
            }
        }

        $em->flush();
        $em->clear();
    }

    public function shiftPositions(): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement('UPDATE item SET position = position + 1');
    }

    public function updatePositionAndShiftOthers(Item $item, int $newPos): void
    {
        $em = $this->getEntityManager();
        $oldPos = (int)$item->getPosition();

        $qb = $this->createQueryBuilder('i')->update();
        if ($newPos < $oldPos) {
            $qb->set('i.position', 'i.position + 1')
               ->where('i.position >= :new AND i.position < :old')
               ->setParameter('new', $newPos)
               ->setParameter('old', $oldPos);
        } else {
            $qb->set('i.position', 'i.position - 1')
               ->where('i.position > :old AND i.position <= :new')
               ->setParameter('old', $oldPos)
               ->setParameter('new', $newPos);
        }
        $qb->getQuery()->execute();

        $item->setPosition($newPos);

        $em->flush();
        $em->clear();
    }
}
