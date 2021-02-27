<?php

namespace App\Repository;

use App\Entity\Mansioni;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mansioni|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mansioni|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mansioni[]    findAll()
 * @method Mansioni[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MansioniRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mansioni::class);
    }

    // /**
    //  * @return Mansioni[] Returns an array of Mansioni objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mansioni
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
