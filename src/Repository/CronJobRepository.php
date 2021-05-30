<?php

namespace App\Repository;

use App\Entity\CronJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CronJobRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, CronJob::class);
  }

  public function findByName(string $name): ?CronJob
  {
    return parent::findBy(['name' => $name])[0] ?? null;
  }
}
