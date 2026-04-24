<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudioActivity>
 */
class StudioActivityRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioActivity::class);
  }

  public function findAllStudioActivities(Studio $studio, ?int $limit = null, int $offset = 0): array
  {
    return $this->findBy(['studio' => $studio], ['created_on' => 'DESC'], $limit, $offset);
  }

  public function findAllStudioActivitiesByActivityType(Studio $studio, string $activityType): array
  {
    return $this->findBy(['studio' => $studio, 'type' => $activityType]);
  }

  public function countStudioActivities(Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }

  public function findStudioActivityById(string $activity_id): ?StudioActivity
  {
    return $this->findOneBy(['id' => $activity_id]);
  }

  /**
   * @param string[] $studioIds
   *
   * @return array<string, int> studio ID => count
   */
  public function countStudioActivitiesBatch(array $studioIds): array
  {
    if ([] === $studioIds) {
      return [];
    }

    $qb = $this->getEntityManager()->createQueryBuilder();
    $rows = $qb->select('IDENTITY(a.studio) AS studio_id, COUNT(a.id) AS cnt')
      ->from(StudioActivity::class, 'a')
      ->where('a.studio IN (:ids)')
      ->setParameter('ids', $studioIds)
      ->groupBy('a.studio')
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[$row['studio_id']] = (int) $row['cnt'];
    }

    return $map;
  }

  public function findAllStudioActivitiesCombined(Studio $studio, ?int $limit = null): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->addselect('a')->from(StudioActivity::class, 'a')
      ->where($qb->expr()->eq('a.studio', ':studio'))
      ->setParameter('studio', $studio)
      ->orderBy('a.created_on', Criteria::DESC)
    ;

    if (null !== $limit) {
      $qb->setMaxResults($limit);
    }

    return $qb->getQuery()->getResult();
  }
}
