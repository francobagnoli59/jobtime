<?php

namespace App\Repository;

use App\Entity\Causali;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Causali|null find($id, $lockMode = null, $lockVersion = null)
 * @method Causali|null findOneBy(array $criteria, array $orderBy = null)
 * @method Causali[]    findAll()
 * @method Causali[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CausaliRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Causali::class);
    }

    // /**
    //  * @return Causali[] Returns an array of Causali objects
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
    public function findOneBySomeField($value): ?Causali
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
