<?php

namespace App\Repository;

use App\Entity\MovimentiAttrezzature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MovimentiAttrezzature|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimentiAttrezzature|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimentiAttrezzature[]    findAll()
 * @method MovimentiAttrezzature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimentiAttrezzatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimentiAttrezzature::class);
    }

    // al momento non è usata:
    public function findLastLocation(int $attrezzatura_id)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('m')
            ->where('m.attrezzatura = :attrezzatura_id')
            ->setParameter('attrezzatura_id', $attrezzatura_id)
            ->orderBy('m.dataMovimento', 'DESC');

        $query = $qb->getQuery();

        // tutti i movimenti
        // return $query->execute();   (: array)

        // to get just one result:
        // $product = $query->setMaxResults(1)->getOneOrNullResult();
        return $query->setMaxResults(1)->getOneOrNullResult();
    }
    // /**
    //  * @return MovimentiAttrezzature[] Returns an array of MovimentiAttrezzature objects
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
    public function findOneBySomeField($value): ?MovimentiAttrezzature
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
