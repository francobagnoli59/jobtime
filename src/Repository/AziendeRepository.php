<?php

namespace App\Repository;

use App\Entity\Aziende;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Aziende|null find($id, $lockMode = null, $lockVersion = null)
 * @method Aziende|null findOneBy(array $criteria, array $orderBy = null)
 * @method Aziende[]    findAll()
 * @method Aziende[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AziendeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aziende::class);
    }

    // /**
    //  * @return Aziende[] Returns an array of Aziende objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Aziende
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
