<?php

namespace App\Api\Services\ResponseCache;

use App\DB\Entity\Api\ResponseCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResponseCache|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponseCache|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponseCache[]    findAll()
 * @method ResponseCache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponseCacheRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ResponseCache::class);
  }

  // /**
    //  * @return ResponseCache[] Returns an array of ResponseCache objects
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
    public function findOneBySomeField($value): ?ResponseCache
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
