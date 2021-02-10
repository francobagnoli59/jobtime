<?php

namespace App\Repository;

use App\Entity\FestivitaAnnuali;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FestivitaAnnuali|null find($id, $lockMode = null, $lockVersion = null)
 * @method FestivitaAnnuali|null findOneBy(array $criteria, array $orderBy = null)
 * @method FestivitaAnnuali[]    findAll()
 * @method FestivitaAnnuali[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FestivitaAnnualiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FestivitaAnnuali::class);
    }

    // /**
    //  * @return FestivitaAnnuali[] Returns an array of FestivitaAnnuali objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FestivitaAnnuali
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
