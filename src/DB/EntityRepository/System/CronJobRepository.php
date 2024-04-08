<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\System;

use App\DB\Entity\System\CronJob;
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
