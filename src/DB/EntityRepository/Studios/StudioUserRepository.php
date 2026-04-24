<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<StudioUser>
 */
class StudioUserRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioUser::class);
  }

  public function findAllStudioUsers(?Studio $studio, ?int $limit = null, int $offset = 0): array
  {
    return $this->findBy(['studio' => $studio, 'status' => StudioUser::STATUS_ACTIVE], null, $limit, $offset);
  }

  public function findStudioAdmin(?Studio $studio): ?StudioUser
  {
    return $this->findOneBy(['studio' => $studio, 'role' => StudioUser::ROLE_ADMIN]);
  }

  public function findStudioUser(?UserInterface $user, Studio $studio): ?StudioUser
  {
    return $this->findOneBy(['studio' => $studio, 'user' => $user]);
  }

  public function countStudioUsers(?Studio $studio): int
  {
    return $this->count(['studio' => $studio, 'status' => 'active']);
  }

  public function countStudioAdmins(Studio $studio): int
  {
    return $this->count(['studio' => $studio, 'role' => StudioUser::ROLE_ADMIN, 'status' => StudioUser::STATUS_ACTIVE]);
  }

  /**
   * @param string[] $studioIds
   *
   * @return array<string, int> studio ID => count
   */
  public function countStudioUsersBatch(array $studioIds): array
  {
    if ([] === $studioIds) {
      return [];
    }

    $qb = $this->getEntityManager()->createQueryBuilder();
    $rows = $qb->select('IDENTITY(su.studio) AS studio_id, COUNT(su.id) AS cnt')
      ->from(StudioUser::class, 'su')
      ->where('su.studio IN (:ids)')
      ->andWhere('su.status = :status')
      ->setParameter('ids', $studioIds)
      ->setParameter('status', 'active')
      ->groupBy('su.studio')
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
