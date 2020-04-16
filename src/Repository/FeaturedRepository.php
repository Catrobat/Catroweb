<?php

namespace App\Repository;

use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Utils\APIQueryHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class FeaturedRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedProgram::class);
  }

  /**
   * @return mixed
   */
  public function getFeaturedPrograms(?string $flavor, ?int $limit = 20, ?int $offset = 0, ?string $platform = null, ?string $max_version = null)
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    $qb->orderBy('e.priority', 'DESC');

    APIQueryHelper::addMaxVersionCondition($qb, $max_version);
    APIQueryHelper::addFlavorCondition($qb, $flavor);
    APIQueryHelper::addPlatformCondition($qb, $platform);

    return $qb->getQuery()->getResult();
  }

  public function getFeaturedProgramsCount(?string $flavor, ?string $platform = null, ?string $max_version = null): int
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('count(e.id)')
      ->where('e.active = true')
      ->andWhere($qb->expr()->isNotNull('e.program'))
    ;
    $qb->orderBy('e.priority', 'DESC');

    APIQueryHelper::addMaxVersionCondition($qb, $max_version);
    APIQueryHelper::addFlavorCondition($qb, $flavor);
    APIQueryHelper::addPlatformCondition($qb, $platform);

    try
    {
      $projects_count = $qb->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   *
   * @return mixed
   */
  public function getFeaturedProgramCount(string $flavor, bool $for_ios = false)
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
   * @return mixed
   */
  public function getFeaturedItems(string $flavor, ?int $limit = 20, int $offset = 0)
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
   * @throws NoResultException
   * @throws NonUniqueResultException
   *
   * @return mixed
   */
  public function getFeaturedItemCount(string $flavor)
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
   * @throws NoResultException
   */
  public function isFeatured(Program $program): bool
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
    catch (NonUniqueResultException $nonUniqueResultException)
    {
      return false;
    }
  }
}
