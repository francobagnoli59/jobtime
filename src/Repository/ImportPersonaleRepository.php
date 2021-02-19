<?php

namespace App\Repository;

use App\Entity\ImportPersonale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ImportPersonale|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportPersonale|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportPersonale[]    findAll()
 * @method ImportPersonale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportPersonaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportPersonale::class);
    }

    // /**
    //  * @return ImportPersonale[] Returns an array of ImportPersonale objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImportPersonale
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
