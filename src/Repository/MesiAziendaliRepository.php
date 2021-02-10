<?php

namespace App\Repository;

use App\Entity\MesiAziendali;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method MesiAziendali|null find($id, $lockMode = null, $lockVersion = null)
 * @method MesiAziendali|null findOneBy(array $criteria, array $orderBy = null)
 * @method MesiAziendali[]    findAll()
 * @method MesiAziendali[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MesiAziendaliRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MesiAziendali::class);
    }

    public function countOpenMonth($azienda): int
    {
        return $this->getVerifyOpenMonth($azienda)->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();
    }

    public function getIdOpenMonth($azienda): int
    {
        return $this->getVerifyOpenMonth($azienda)->select('m.id')->getQuery()->getSingleScalarResult();;
    }

    private function getVerifyOpenMonth($azienda): QueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isHoursCompleted = :state ')
            ->andWhere('m.azienda = :azienda')
            ->setParameters([
                'state' => 'false',
                'azienda' => $azienda,
            ])
        ;
    }

    // /**
    //  * @return MesiAziendali[] Returns an array of MesiAziendali objects
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
    public function findOneBySomeField($value): ?MesiAziendali
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
