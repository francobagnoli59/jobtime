<?php

namespace App\Repository;

use App\Entity\DocumentiPersonale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentiPersonale|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentiPersonale|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentiPersonale[]    findAll()
 * @method DocumentiPersonale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentiPersonaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentiPersonale::class);
    }

    // /**
    //  * @return DocumentiPersonale[] Returns an array of DocumentiPersonale objects
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
    public function findOneBySomeField($value): ?DocumentiPersonale
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
