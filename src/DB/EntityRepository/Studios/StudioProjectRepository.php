<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

  public function countStudioProjects(?Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
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
  public function countStudioProjectsBatch(array $studioIds): array
  {
    if ([] === $studioIds) {
      return [];
    }

    $qb = $this->getEntityManager()->createQueryBuilder();
    $rows = $qb->select('IDENTITY(sp.studio) AS studio_id, COUNT(sp.id) AS cnt')
      ->from(StudioProject::class, 'sp')
      ->where('sp.studio IN (:ids)')
      ->setParameter('ids', $studioIds)
      ->groupBy('sp.studio')
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[$row['studio_id']] = (int) $row['cnt'];
    }

    return $map;
  }
}
