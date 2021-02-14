<?php

namespace App\Repository;

use App\Entity\ConsolidatiCantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConsolidatiCantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsolidatiCantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsolidatiCantieri[]    findAll()
 * @method ConsolidatiCantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsolidatiCantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsolidatiCantieri::class);
    }

    // /**
    //  * @return ConsolidatiCantieri[] Returns an array of ConsolidatiCantieri objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConsolidatiCantieri
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
