<?php

namespace App\Repository;

use App\Entity\ImportCantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ImportCantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportCantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportCantieri[]    findAll()
 * @method ImportCantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportCantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportCantieri::class);
    }

    // /**
    //  * @return ImportCantieri[] Returns an array of ImportCantieri objects
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
    public function findOneBySomeField($value): ?ImportCantieri
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
