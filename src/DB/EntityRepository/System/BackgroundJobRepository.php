<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\System;

use App\DB\Entity\System\BackgroundJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BackgroundJob>
 */
class BackgroundJobRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, BackgroundJob::class);
  }

  /** A queued or running job for the same work, which a new request joins instead of queueing it twice. */
  public function findUnfinished(string $type, string $subject): ?BackgroundJob
  {
    return $this->createQueryBuilder('j')
      ->where('j.type = :type')->andWhere('j.subject = :subject')->andWhere('j.state IN (:states)')
      ->setParameter('type', $type)->setParameter('subject', $subject)
      ->setParameter('states', [BackgroundJob::QUEUED, BackgroundJob::RUNNING])
      ->setMaxResults(1)
      ->getQuery()->getOneOrNullResult();
  }

  /** Clients poll a job for minutes at most; finished rows are only kept for a while. */
  public function deleteFinishedBefore(\DateTimeImmutable $before): int
  {
    return (int) $this->createQueryBuilder('j')->delete()
      ->where('j.state IN (:states)')->andWhere('j.updated_at < :before')
      ->setParameter('states', [BackgroundJob::DONE, BackgroundJob::FAILED])->setParameter('before', $before)
      ->getQuery()->execute();
  }
}
