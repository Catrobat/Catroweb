<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project\Special;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\FeaturedProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class FeaturedRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedProgram::class);
  }

  public function getFeaturedPrograms(?string $flavor, ?int $limit = 20, ?int $offset = 0, ?string $platform = null, ?string $max_version = null): mixed
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
    $qb->leftJoin('e.program', 'program');
    $qb = $this->addMaxVersionCondition($qb, $max_version);
    $qb = $this->addFeaturedExampleFlavorCondition($qb, $flavor);
    $qb = $this->addPlatformCondition($qb, $platform);

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
    $qb->leftJoin('e.program', 'program');
    $qb = $this->addMaxVersionCondition($qb, $max_version);
    $qb = $this->addFeaturedExampleFlavorCondition($qb, $flavor);
    $qb = $this->addPlatformCondition($qb, $platform);

    try {
      $projects_count = $qb->getQuery()->getSingleScalarResult();
    } catch (NonUniqueResultException|NoResultException) {
      $projects_count = 0;
    }

    return $projects_count;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function getFeaturedProgramCount(string $flavor, bool $for_ios = false): mixed
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select($qb->expr()->count('e.id'))
      ->join('e.flavor', 'fl')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('fl.name', ':flavor'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->andWhere($qb->expr()->eq('e.for_ios', ':for_ios'))
      ->setParameter('flavor', $flavor)
      ->setParameter('for_ios', $for_ios)
    ;

    return $qb->getQuery()->getSingleScalarResult();
  }

  public function getFeaturedItems(string $flavor, ?int $limit = 20, int $offset = 0): mixed
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->join('e.flavor', 'fl')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('fl.name', ':flavor'))
      ->andWhere($qb->expr()->eq('e.for_ios', 'false'))
      ->setParameter('flavor', $flavor)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->orderBy('e.priority', 'DESC')
      ->getQuery()->getResult()
    ;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function getFeaturedItemCount(string $flavor): mixed
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select($qb->expr()->count('e.id'))
      ->join('e.flavor', 'fl')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('fl.name', ':flavor'))
      ->andWhere($qb->expr()->eq('e.for_ios', 'false'))
      ->setParameter('flavor', $flavor)
      ->getQuery()->getSingleScalarResult()
    ;
  }

  public function isFeatured(Program $program): bool
  {
    $qb = $this->createQueryBuilder('e');
    $qb
      ->select('count(e.id)')
      ->where($qb->expr()->eq('e.program', ':program'))
      ->setParameter('program', $program)
    ;
    try {
      $count = intval($qb->getQuery()->getSingleScalarResult());

      return $count > 0;
    } catch (NonUniqueResultException|NoResultException) {
      return false;
    }
  }

  private function addPlatformCondition(QueryBuilder $query_builder, ?string $platform = null): QueryBuilder
  {
    if (null !== $platform && '' !== trim($platform)) {
      if ('android' === $platform) {
        $query_builder
          ->andWhere($query_builder->expr()->eq('e.for_ios', ':for_ios'))
          ->setParameter('for_ios', false)
        ;
      } else {
        $query_builder
          ->andWhere($query_builder->expr()->eq('e.for_ios', ':for_ios'))
          ->setParameter('for_ios', true)
        ;
      }
    }

    return $query_builder;
  }

  private function addFeaturedExampleFlavorCondition(QueryBuilder $query_builder, ?string $flavor = null): QueryBuilder
  {
    if (null !== $flavor && '' !== trim($flavor)) {
      $where = 'fl.name = :name';
      $query_builder
        ->join('e.flavor', 'fl')
        ->andWhere($where)
        ->setParameter('name', $flavor)
      ;
    }

    return $query_builder;
  }

  private function addMaxVersionCondition(QueryBuilder $query_builder, ?string $max_version = null): QueryBuilder
  {
    if (null === $max_version || '' === $max_version) {
      return $query_builder;
    }

    $query_builder
      ->innerJoin(Program::class, 'p', Join::WITH,
        $query_builder->expr()->eq('e.program', 'p')->__toString())
      ->andWhere($query_builder->expr()->lte('p.language_version', ':max_version'))
      ->setParameter('max_version', $max_version)
      ->addOrderBy('e.id', 'ASC')
      ->addOrderBy('e.priority', 'DESC')
    ;

    return $query_builder;
  }
}
