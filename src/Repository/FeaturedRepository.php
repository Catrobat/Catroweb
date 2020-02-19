<?php

namespace App\Repository;

use App\Entity\FeaturedProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FeaturedRepository.
 */
class FeaturedRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedProgram::class);
  }

  /**
   * @param      $flavor
   * @param int  $limit
   * @param int  $offset
   * @param bool $for_ios
   *
   * @return mixed
   */
  public function getFeaturedPrograms($flavor, $limit = 20, $offset = 0, $for_ios = false)
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->andWhere($qb->expr()->eq('e.for_ios', ':for_ios'))
      ->setParameter('flavor', $flavor)
      ->setParameter('for_ios', $for_ios)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $qb->orderBy('e.priority', 'DESC');

    return $qb->getQuery()->getResult();
  }

  /**
   * @param      $flavor
   * @param bool $for_ios
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   *
   * @return mixed
   */
  public function getFeaturedProgramCount($flavor, $for_ios = false)
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select($qb->expr()->count('e.id'))
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->andWhere($qb->expr()->eq('e.for_ios', ':for_ios'))
      ->setParameter('flavor', $flavor)
      ->setParameter('for_ios', $for_ios)
    ;

    return $qb->getQuery()->getSingleScalarResult();
  }

  /**
   * @param     $flavor
   * @param int $limit
   * @param int $offset
   *
   * @return mixed
   */
  public function getFeaturedItems($flavor, $limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('e.for_ios', 'false'))
      ->setParameter('flavor', $flavor)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->orderBy('e.priority', 'DESC')
      ->getQuery()->getResult();
  }

  /**
   * @param $flavor
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   *
   * @return mixed
   */
  public function getFeaturedItemCount($flavor)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select($qb->expr()->count('e.id'))
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('e.for_ios', 'false'))
      ->setParameter('flavor', $flavor)
      ->getQuery()->getSingleScalarResult();
  }

  /**
   * @param $program
   *
   * @return bool
   */
  public function isFeatured($program)
  {
    $qb = $this->createQueryBuilder('e');
    $qb
      ->select('count(e.id)')
      ->where($qb->expr()->eq('e.program', ':program'))
      ->setParameter('program', $program)
    ;
    try
    {
      $count = $qb->getQuery()->getSingleScalarResult();

      return $count > 0;
    }
    catch (NonUniqueResultException $exception)
    {
      return false;
    }
  }
}
