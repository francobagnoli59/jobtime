<?php

namespace App\Repository;

use App\Entity\Attrezzature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attrezzature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attrezzature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attrezzature[]    findAll()
 * @method Attrezzature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttrezzatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attrezzature::class);
    }

    // /**
    //  * @return Attrezzature[] Returns an array of Attrezzature objects
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
    public function findOneBySomeField($value): ?Attrezzature
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
