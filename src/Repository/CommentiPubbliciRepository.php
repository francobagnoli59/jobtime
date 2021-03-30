<?php

namespace App\Repository;

use App\Entity\CommentiPubblici;
use App\Entity\Cantieri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method CommentiPubblici|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentiPubblici|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentiPubblici[]    findAll()
 * @method CommentiPubblici[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentiPubbliciRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
    
        parent::__construct($registry, CommentiPubblici::class);
    }

    public function getCommentPaginator(Cantieri $cantieri, int $offset): Paginator
    {
            $query = $this->createQueryBuilder('c')
           ->andWhere('c.cantieri = :cantieri')
            ->setParameter('cantieri', $cantieri)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
            ;
            
            return new Paginator($query); 
        
    }

    // /**
    //  * @return CommentiPubblici[] Returns an array of CommentiPubblici objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommentiPubblici
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
