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

  /** Longer than any job's runs and retries take: an unfinished job not touched since is dead (a killed worker). */
  final public const string STALE_AFTER = '-30 minutes';

  /** A live queued or running job for the same work, which a new request joins instead of queueing it twice. */
  public function findUnfinished(string $type, string $subject): ?BackgroundJob
  {
    return $this->createQueryBuilder('j')
      ->where('j.type = :type')->andWhere('j.subject = :subject')->andWhere('j.state IN (:states)')->andWhere('j.updated_at > :stale')
      ->setParameter('type', $type)->setParameter('subject', $subject)
      ->setParameter('states', [BackgroundJob::QUEUED, BackgroundJob::RUNNING])
      ->setParameter('stale', new \DateTimeImmutable(self::STALE_AFTER))
      ->setMaxResults(1)
      ->getQuery()->getOneOrNullResult()
    ;
  }

  /** Ends jobs left unfinished by a killed worker, so their clients stop polling. */
  public function failStale(): int
  {
    return (int) $this->createQueryBuilder('j')->update()
      ->set('j.state', ':failed')->set('j.error_status', 500)->set('j.error_message', ':message')->set('j.updated_at', ':now')
      ->where('j.state IN (:states)')->andWhere('j.updated_at < :stale')
      ->setParameter('failed', BackgroundJob::FAILED)->setParameter('message', 'Something went wrong. Please try again later.')
      ->setParameter('now', new \DateTimeImmutable())->setParameter('states', [BackgroundJob::QUEUED, BackgroundJob::RUNNING])
      ->setParameter('stale', new \DateTimeImmutable(self::STALE_AFTER))
      ->getQuery()->execute()
    ;
  }

  /** Clients poll a job for minutes at most; finished rows are only kept for a while. */
  public function deleteFinishedBefore(\DateTimeImmutable $before): int
  {
    return (int) $this->createQueryBuilder('j')->delete()
      ->where('j.state IN (:states)')->andWhere('j.updated_at < :before')
      ->setParameter('states', [BackgroundJob::DONE, BackgroundJob::FAILED])->setParameter('before', $before)
      ->getQuery()->execute()
    ;
  }
}
