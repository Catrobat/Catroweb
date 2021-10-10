<?php

namespace App\Repository\Studios;

use App\Entity\Studio;
use App\Entity\StudioActivity;
use App\Entity\StudioProgram;
use App\Entity\StudioUser;
use App\Entity\UserComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
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

  public function countStudioActivities(Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }

  public function findStudioActivityById(int $activity_id): ?StudioActivity
  {
    return $this->findOneBy(['id' => $activity_id]);
  }

  public function findAllStudioActivitiesCombined(Studio $studio): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    return $qb->addselect('a')->from(StudioActivity::class, 'a')
      ->where($qb->expr()->eq('a.studio', "'".$studio->getId()."'"))
      ->orderBy('a.created_on', Criteria::DESC)
      ->getQuery()
      ->getResult()
    ;
  }
}
