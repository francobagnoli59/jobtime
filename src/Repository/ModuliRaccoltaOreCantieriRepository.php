<?php

namespace App\Repository;

use App\Entity\ModuliRaccoltaOreCantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method ModuliRaccoltaOreCantieri|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuliRaccoltaOreCantieri|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuliRaccoltaOreCantieri[]    findAll()
 * @method ModuliRaccoltaOreCantieri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuliRaccoltaOreCantieriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuliRaccoltaOreCantieri::class);
    }

    public function deleteOreCantieri($raccoltaOre): int
    {
        return $this->getIfRaccoltaOreQueryBuilder($raccoltaOre)->delete()->getQuery()->execute();
    }

    private function getIfRaccoltaOreQueryBuilder($raccoltaOre): QueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.raccoltaOrePersona = :raccoltaOre')
            ->setParameters([
                'raccoltaOre' => $raccoltaOre,
            ])
        ;
    }
    // /**
    //  * @return ModuliRaccoltaOreCantieri[] Returns an array of ModuliRaccoltaOreCantieri objects
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
    public function findOneBySomeField($value): ?ModuliRaccoltaOreCantieri
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
