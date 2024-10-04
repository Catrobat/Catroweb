<?php

namespace App\DB\EntityRepository\System;

use App\DB\Entity\System\Statistic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Statistic>
 */
class StatisticRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Statistic::class);
  }

  public function incrementProjects(): void
  {
    $this->getEntityManager()
      ->createQuery('UPDATE App\DB\Entity\System\Statistic s SET s.projects = s.projects + 1 WHERE s.id = 1')
      ->execute()
    ;
  }

  public function incrementUser(): void
  {
    $this->getEntityManager()
      ->createQuery('UPDATE App\DB\Entity\System\Statistic s SET s.users = s.users + 1 WHERE s.id = 1')
      ->execute()
    ;
  }
}
