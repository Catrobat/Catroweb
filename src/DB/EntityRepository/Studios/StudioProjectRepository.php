<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioProject;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<StudioProject>
 */
class StudioProjectRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioProject::class);
  }

  public function findAllStudioProjects(Studio $studio, ?int $limit = null, int $offset = 0): array
  {
    return $this->findBy(['studio' => $studio], null, $limit, $offset);
  }

  public function findStudioProject(Studio $studio, Project $project): ?StudioProject
  {
    return $this->findOneBy(['studio' => $studio, 'project' => $project]);
  }

  /**
   * Which studio projects a viewer may see: publicly visible ones, plus their own
   * hidden/private ones; minors additionally only see not-for-kids projects they own.
   * The query must join the project as $project_alias.
   */
  public static function applyViewerVisibility(QueryBuilder $qb, ?User $viewer, string $project_alias = 'p'): void
  {
    $p = $project_alias;
    $publicly_visible = "{$p}.visible = true AND {$p}.auto_hidden = false AND {$p}.private = false";
    if (!$viewer instanceof User) {
      $qb->andWhere($publicly_visible);

      return;
    }

    $qb->andWhere("({$publicly_visible}) OR {$p}.user = :viewer")
      ->setParameter('viewer', $viewer)
    ;
    if ($viewer->isMinor()) {
      $qb->andWhere("{$p}.not_for_kids = 0 OR {$p}.user = :viewer");
    }
  }

  public function countStudioProjects(?Studio $studio, ?User $viewer = null): int
  {
    $qb = $this->getEntityManager()->createQueryBuilder()
      ->select('COUNT(sp.id)')
      ->from(StudioProject::class, 'sp')
      ->join('sp.project', 'p')
      ->where('sp.studio = :studio')
      ->setParameter('studio', $studio)
    ;
    self::applyViewerVisibility($qb, $viewer);

    return (int) $qb->getQuery()->getSingleScalarResult();
  }

  public function countStudioUserProjects(?Studio $studio, ?UserInterface $user): int
  {
    return $this->count(['studio' => $studio, 'user' => $user]);
  }

  /**
   * @param string[] $studioIds
   *
   * @return array<string, int> studio ID => count
   */
  public function countStudioProjectsBatch(array $studioIds, ?User $viewer = null): array
  {
    if ([] === $studioIds) {
      return [];
    }

    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('IDENTITY(sp.studio) AS studio_id, COUNT(sp.id) AS cnt')
      ->from(StudioProject::class, 'sp')
      ->join('sp.project', 'p')
      ->where('sp.studio IN (:ids)')
      ->setParameter('ids', $studioIds)
      ->groupBy('sp.studio')
    ;
    self::applyViewerVisibility($qb, $viewer);
    $rows = $qb->getQuery()->getArrayResult();

    $map = [];
    foreach ($rows as $row) {
      $map[$row['studio_id']] = (int) $row['cnt'];
    }

    return $map;
  }
}
