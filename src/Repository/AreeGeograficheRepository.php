<?php

namespace App\Repository;

use App\Entity\AreeGeografiche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AreeGeografiche|null find($id, $lockMode = null, $lockVersion = null)
 * @method AreeGeografiche|null findOneBy(array $criteria, array $orderBy = null)
 * @method AreeGeografiche[]    findAll()
 * @method AreeGeografiche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AreeGeograficheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AreeGeografiche::class);
    }

    // /**
    //  * @return AreeGeografiche[] Returns an array of AreeGeografiche objects
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
    public function findOneBySomeField($value): ?AreeGeografiche
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
