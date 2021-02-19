<?php

namespace App\Repository;

use App\Entity\OreLavorate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method OreLavorate|null find($id, $lockMode = null, $lockVersion = null)
 * @method OreLavorate|null findOneBy(array $criteria, array $orderBy = null)
 * @method OreLavorate[]    findAll()
 * @method OreLavorate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OreLavorateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OreLavorate::class);
    }

    public function countConfirmed($azienda, $state, $datestart, $dateend): int
    {
        return $this->getIfConfirmedQueryBuilder($azienda, $state, $datestart, $dateend)->select('COUNT(ol.id)')->getQuery()->getSingleScalarResult();
    }
    
    public function deleteOreLavorate($azienda, $state, $datestart, $dateend): int
    {
        return $this->getIfConfirmedQueryBuilder($azienda, $state, $datestart, $dateend)->delete()->getQuery()->execute();
    }

    private function getIfConfirmedQueryBuilder($azienda, $state, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('ol')
            ->andWhere('ol.isConfirmed = :state ')
            ->andWhere('ol.giorno >= :datestart')
            ->andWhere('ol.giorno <= :dateend')
            ->andWhere('ol.azienda = :azienda')
            ->setParameters([
                'state' => $state,
                'datestart' => $datestart,
                'dateend' => $dateend,
                'azienda' => $azienda,
            ])
        ;
    }


    public function countPersonaConfirmed($persona, $state, $datestart, $dateend): int
    {
        return $this->getIfConfirmedPersonaQueryBuilder($persona, $state, $datestart, $dateend)->select('COUNT(ol.id)')->getQuery()->getSingleScalarResult();
    }

    private function getIfConfirmedPersonaQueryBuilder($persona, $state, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('ol')
            ->andWhere('ol.isConfirmed = :state ')
            ->andWhere('ol.giorno >= :datestart')
            ->andWhere('ol.giorno <= :dateend')
            ->andWhere('ol.persona = :persona')
            ->setParameters([
                'state' => $state,
                'datestart' => $datestart,
                'dateend' => $dateend,
                'persona' => $persona,
            ])
          
        ;
    }
  
    public function collectionPersonaConfirmed($persona, $state, $datestart, $dateend): array
    {
        $query = $this->getMonthPersonaQueryBuilder($persona, $state, $datestart, $dateend)->select('ol')->getQuery();
        $subSetOreLavPersona = $query->getResult();
        return $subSetOreLavPersona;
    }
// 'ol.cantiere, ol.causale, ol.oreRegistrate, ol.orePianificate'

    private function getMonthPersonaQueryBuilder($persona, $state, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('ol')
            ->andWhere('ol.isConfirmed = :state ')
            ->andWhere('ol.giorno >= :datestart')
            ->andWhere('ol.giorno <= :dateend')
            ->andWhere('ol.persona = :persona')
            ->orderBy('ol.giorno', 'ASC')
            ->addOrderBy('ol.causale', 'ASC')
            ->setParameters([
                'state' => $state,
                'datestart' => $datestart,
                'dateend' => $dateend,
                'persona' => $persona,
            ])
          
        ;
    }

    public function setMonthPersonaTransfer($persona, $state, $datestart, $dateend): int
    {
        return $this->setTransferMonthPersonaQueryBuilder($persona, $state, $datestart, $dateend)->update()->getQuery()->execute();
        
    }

    private function setTransferMonthPersonaQueryBuilder($persona, $state, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('ol')
            ->andWhere('ol.isTransfer = :state ')
            ->andWhere('ol.giorno >= :datestart')
            ->andWhere('ol.giorno <= :dateend')
            ->andWhere('ol.persona = :persona')
            ->set('ol.isTransfer', ':setstate')
            ->setParameters([
                'setstate' => !$state,
                'state' => $state,
                'datestart' => $datestart,
                'dateend' => $dateend,
                'persona' => $persona,
            ])
        ;
    }
    // /**
    //  * @return OreLavorate[] Returns an array of OreLavorate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OreLavorate
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
