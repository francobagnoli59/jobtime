<?php

namespace App\Repository;

use App\Entity\PianoOreCantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PianoOreCantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method PianoOreCantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method PianoOreCantieri[]    findAll()
 * @method PianoOreCantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PianoOreCantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PianoOreCantieri::class);
    }

    // /**
    //  * @return PianoOreCantieri[] Returns an array of PianoOreCantieri objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PianoOreCantieri
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
