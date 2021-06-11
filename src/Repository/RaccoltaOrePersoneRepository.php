<?php

namespace App\Repository;

use App\Entity\RaccoltaOrePersone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RaccoltaOrePersone|null find($id, $lockMode = null, $lockVersion = null)
 * @method RaccoltaOrePersone|null findOneBy(array $criteria, array $orderBy = null)
 * @method RaccoltaOrePersone[]    findAll()
 * @method RaccoltaOrePersone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaccoltaOrePersoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaccoltaOrePersone::class);
    }

    // /**
    //  * @return RaccoltaOrePersone[] Returns an array of RaccoltaOrePersone objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RaccoltaOrePersone
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
