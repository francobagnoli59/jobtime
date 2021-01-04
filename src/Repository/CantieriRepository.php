<?php

namespace App\Repository;

use App\Entity\Cantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cantieri[]    findAll()
 * @method Cantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cantieri::class);
    }

    // /**
    //  * @return Cantieri[] Returns an array of Cantieri objects
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
    public function findOneBySomeField($value): ?Cantieri
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
