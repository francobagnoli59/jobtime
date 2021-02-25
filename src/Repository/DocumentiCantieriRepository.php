<?php

namespace App\Repository;

use App\Entity\DocumentiCantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentiCantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentiCantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentiCantieri[]    findAll()
 * @method DocumentiCantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentiCantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentiCantieri::class);
    }

    // /**
    //  * @return DocumentiCantieri[] Returns an array of DocumentiCantieri objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DocumentiCantieri
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
