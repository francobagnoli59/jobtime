<?php

namespace App\Repository;

use App\Entity\ConsolidatiPersonale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConsolidatiPersonale|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsolidatiPersonale|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsolidatiPersonale[]    findAll()
 * @method ConsolidatiPersonale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsolidatiPersonaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsolidatiPersonale::class);
    }

    // /**
    //  * @return ConsolidatiPersonale[] Returns an array of ConsolidatiPersonale objects
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
    public function findOneBySomeField($value): ?ConsolidatiPersonale
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
