<?php

namespace App\Repository;

use App\Entity\RegoleFatturazione;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegoleFatturazione|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegoleFatturazione|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegoleFatturazione[]    findAll()
 * @method RegoleFatturazione[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegoleFatturazioneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegoleFatturazione::class);
    }

    // /**
    //  * @return RegoleFatturazione[] Returns an array of RegoleFatturazione objects
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
    public function findOneBySomeField($value): ?RegoleFatturazione
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
