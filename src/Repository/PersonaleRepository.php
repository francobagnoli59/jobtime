<?php

namespace App\Repository;

use App\Entity\Personale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Personale|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personale|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personale[]    findAll()
 * @method Personale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personale::class);
    }

    public function collectionPersScadDeterminato($azienda, $datestart, $dateend): array
    {
        $query = $this->getPersScadDeterminatoQueryBuilder($azienda, $datestart, $dateend)->select('pe')->getQuery();
        $subSetPersone = $query->getResult();
        return $subSetPersone;
    }

    private function getPersScadDeterminatoQueryBuilder($azienda, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('pe')
            ->andWhere('pe.isEnforce = true ')
            ->andWhere('pe.azienda = :azienda')
            ->andWhere('pe.scadenzaContratto >= :datestart')
            ->andWhere('pe.scadenzaContratto <= :dateend')
            ->orderBy('pe.scadenzaContratto', 'ASC')
            ->addOrderBy('pe.surname', 'ASC')
            ->setParameters([
                'datestart' => $datestart,
                'dateend' => $dateend,
                'azienda' => $azienda,
            ])
          
        ;
    }

    public function collectionPersScadVisita($azienda, $datestart, $dateend): array
    {
        $query = $this->getPersScadVisitaQueryBuilder($azienda, $datestart, $dateend)->select('pe')->getQuery();
        $subSetPersone = $query->getResult();
        return $subSetPersone;
    }

    private function getPersScadVisitaQueryBuilder($azienda, $datestart, $dateend): QueryBuilder
    {
        return $this->createQueryBuilder('pe')
            ->andWhere('pe.isEnforce = true ')
            ->andWhere('pe.azienda = :azienda')
            ->andWhere('pe.scadenzaVisitaMedica >= :datestart')
            ->andWhere('pe.scadenzaVisitaMedica <= :dateend')
            ->orderBy('pe.scadenzaVisitaMedica', 'ASC')
            ->addOrderBy('pe.surname', 'ASC')
            ->setParameters([
                'datestart' => $datestart,
                'dateend' => $dateend,
                'azienda' => $azienda,
            ])
          
        ;
    }
    // /**
    //  * @return Personale[] Returns an array of Personale objects
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
    public function findOneBySomeField($value): ?Personale
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
