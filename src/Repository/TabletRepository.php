<?php

namespace App\Repository;

use App\Entity\Tablet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tablet>
 *
 * @method Tablet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tablet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tablet[]    findAll()
 * @method Tablet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TabletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tablet::class);
    }

    //    /**
    //     * @return Tablet[] Returns an array of Tablet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tablet
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
