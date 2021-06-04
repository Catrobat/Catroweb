<?php

namespace App\Repository\Studios;

use App\Entity\Studio;
use App\Entity\StudioActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudioActivityRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioActivity::class);
  }

  public function findAllStudioActivities(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio]);
  }

  public function findAllStudioActivitiesByActivityType(Studio $studio, string $activityType): array
  {
    return $this->findBy(['studio' => $studio, 'type' => $activityType]);
  }

  public function findStudioActivitiesCount(Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }

  public function findStudioActivityById(int $activity_id): ?StudioActivity
  {
    return $this->findOneBy(['id' => $activity_id]);
  }
}
