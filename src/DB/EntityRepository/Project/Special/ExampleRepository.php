<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project\Special;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\ExampleProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ExampleRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ExampleProgram::class);
  }

  /**
   * @return Program[]
   */
  public function getExampleProjects(bool $debug_build, ?string $flavor, ?int $limit = 20, ?int $offset = 0, ?string $platform = null, ?string $max_version = null): array
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

    return $qb->getQuery()->getResult();
  }

  public function getExampleProjectsCount(bool $debug_build, ?string $flavor, ?string $max_version = null): int
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
  public function getExampleProgramCount(string $flavor, bool $for_ios = false): mixed
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

  public function getExampleItems(string $flavor, ?int $limit = 20, int $offset = 0): mixed
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
  public function getExampleItemCount(string $flavor): mixed
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

  /**
   * @throws NoResultException
   */
  public function isExample(Program $program): bool
  {
    $qb = $this->createQueryBuilder('e');
    $qb
      ->select('count(e.id)')
      ->where($qb->expr()->eq('e.program', ':program'))
      ->setParameter('program', $program)
    ;
    try {
      $count = $qb->getQuery()->getSingleScalarResult();

      return $count > 0;
    } catch (NonUniqueResultException) {
      return false;
    }
  }

  private function addFeaturedExampleFlavorCondition(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e', bool $include_pocketcode = false): QueryBuilder
  {
    if (null !== $flavor && '' !== trim($flavor)) {
      $where = 'fl.name = :name';
      if ($include_pocketcode) {
        $where .= ' OR fl.name = \'pocketcode\'';
      }
      $query_builder
        ->join($alias.'.flavor', 'fl')
        ->andWhere($where)
        ->setParameter('name', $flavor)
      ;
    }

    return $query_builder;
  }

  private function addMaxVersionCondition(QueryBuilder $query_builder, ?string $max_version = null): QueryBuilder
  {
    if (null !== $max_version && '' !== $max_version) {
      $query_builder
        ->innerJoin(Program::class, 'p', Join::WITH,
          $query_builder->expr()->eq('e.program', 'p')->__toString())
        ->andWhere($query_builder->expr()->lte('p.language_version', ':max_version'))
        ->setParameter('max_version', $max_version)
        ->addOrderBy('e.id', 'ASC')
        ->addOrderBy('e.priority', 'DESC')
      ;
    }

    return $query_builder;
  }
}
