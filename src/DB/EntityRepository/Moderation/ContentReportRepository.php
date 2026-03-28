<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Moderation;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Enum\ReportState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContentReport>
 */
class ContentReportRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ContentReport::class);
  }

  public function getCumulativeTrustScore(string $content_type, string $content_id): float
  {
    $qb = $this->createQueryBuilder('r');
    $result = $qb
      ->select('SUM(r.reporter_trust_score)')
      ->where('r.content_type = :content_type')
      ->andWhere('r.content_id = :content_id')
      ->andWhere('r.state = :state')
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->setParameter('state', ReportState::New->value)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return (float) ($result ?? 0.0);
  }

  /**
   * Returns time-weighted accuracy counts for trust score temporal decay.
   *
   * @return array{accepted: float, rejected: float}
   */
  public function getWeightedReportAccuracy(string $user_id): array
  {
    $qb = $this->createQueryBuilder('r');
    $now = new \DateTime();
    $one_year_ago = (clone $now)->modify('-1 year');
    $two_years_ago = (clone $now)->modify('-2 years');

    $result = $qb
      ->select(
        'r.state',
        'SUM(CASE WHEN r.resolved_at >= :one_year_ago THEN 1.0 '.
        'WHEN r.resolved_at >= :two_years_ago THEN 0.5 '.
        'ELSE 0.25 END) as weighted_cnt'
      )
      ->join('r.reporter', 'u')
      ->where('u.id = :user_id')
      ->andWhere('r.state IN (:states)')
      ->setParameter('user_id', $user_id)
      ->setParameter('states', [ReportState::Accepted->value, ReportState::Rejected->value])
      ->setParameter('one_year_ago', $one_year_ago)
      ->setParameter('two_years_ago', $two_years_ago)
      ->groupBy('r.state')
      ->getQuery()
      ->getResult()
    ;

    $counts = ['accepted' => 0.0, 'rejected' => 0.0];
    foreach ($result as $row) {
      if (ReportState::Accepted->value === $row['state']) {
        $counts['accepted'] = (float) $row['weighted_cnt'];
      } elseif (ReportState::Rejected->value === $row['state']) {
        $counts['rejected'] = (float) $row['weighted_cnt'];
      }
    }

    return $counts;
  }

  /**
   * Counts distinct reporters for content within a time window (for brigading detection).
   */
  public function getRecentReportVelocity(string $content_type, string $content_id, int $minute_window): int
  {
    $since = new \DateTime()->modify("-{$minute_window} minutes");

    $qb = $this->createQueryBuilder('r');

    return (int) $qb
      ->select('COUNT(DISTINCT IDENTITY(r.reporter))')
      ->where('r.content_type = :content_type')
      ->andWhere('r.content_id = :content_id')
      ->andWhere('r.state = :state')
      ->andWhere('r.created_at >= :since')
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->setParameter('state', ReportState::New->value)
      ->setParameter('since', $since)
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }

  public function hasUserAlreadyReported(string $user_id, string $content_type, string $content_id): bool
  {
    $qb = $this->createQueryBuilder('r');

    return (int) $qb
      ->select('COUNT(r.id)')
      ->join('r.reporter', 'u')
      ->where('u.id = :user_id')
      ->andWhere('r.content_type = :content_type')
      ->andWhere('r.content_id = :content_id')
      ->setParameter('user_id', $user_id)
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->getQuery()
      ->getSingleScalarResult() > 0
    ;
  }

  /**
   * Checks whether other reports (excluding the given report) exist for the same content
   * that are still pending (New) or have been accepted.
   */
  public function hasOtherActiveReports(string $content_type, string $content_id, int $exclude_report_id): bool
  {
    $qb = $this->createQueryBuilder('r');

    return (int) $qb
      ->select('COUNT(r.id)')
      ->where('r.content_type = :content_type')
      ->andWhere('r.content_id = :content_id')
      ->andWhere('r.id != :exclude_id')
      ->andWhere('r.state IN (:states)')
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->setParameter('exclude_id', $exclude_report_id)
      ->setParameter('states', [ReportState::New->value, ReportState::Accepted->value])
      ->getQuery()
      ->getSingleScalarResult() > 0
    ;
  }

  /**
   * @return ContentReport[]
   */
  public function findPendingReports(
    int $limit,
    ?\DateTimeInterface $cursor_created_at = null,
    ?int $cursor_id = null,
    ?int $legacy_cursor_id = null,
  ): array {
    $qb = $this->createQueryBuilder('r');
    $qb
      ->where('r.state = :state')
      ->setParameter('state', ReportState::New->value)
      ->orderBy('r.created_at', 'ASC')
      ->addOrderBy('r.id', 'ASC')
      ->setMaxResults($limit + 1)
    ;

    if ($cursor_created_at instanceof \DateTimeInterface && null !== $cursor_id) {
      $qb->andWhere(
        $qb->expr()->orX(
          $qb->expr()->gt('r.created_at', ':cursor_created_at'),
          $qb->expr()->andX(
            $qb->expr()->eq('r.created_at', ':cursor_created_at'),
            $qb->expr()->gt('r.id', ':cursor_id')
          )
        )
      )
        ->setParameter('cursor_created_at', $cursor_created_at)
        ->setParameter('cursor_id', $cursor_id)
      ;
    } elseif (null !== $legacy_cursor_id) {
      $qb
        ->andWhere('r.id > :legacy_cursor_id')
        ->setParameter('legacy_cursor_id', $legacy_cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * @return ContentReport[]
   */
  public function findReportsByUser(
    string $user_id,
    int $limit,
    ?\DateTimeInterface $cursor_created_at = null,
    ?int $cursor_id = null,
  ): array {
    $qb = $this->createQueryBuilder('r');
    $qb
      ->where('IDENTITY(r.reporter) = :user_id')
      ->setParameter('user_id', $user_id)
      ->orderBy('r.created_at', 'DESC')
      ->addOrderBy('r.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if ($cursor_created_at instanceof \DateTimeInterface && null !== $cursor_id) {
      $qb->andWhere(
        $qb->expr()->orX(
          $qb->expr()->lt('r.created_at', ':cursor_created_at'),
          $qb->expr()->andX(
            $qb->expr()->eq('r.created_at', ':cursor_created_at'),
            $qb->expr()->lt('r.id', ':cursor_id')
          )
        )
      )
        ->setParameter('cursor_created_at', $cursor_created_at)
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * @return ContentReport[]
   */
  public function findReportsForContent(string $content_type, string $content_id): array
  {
    return $this->findBy(
      ['content_type' => $content_type, 'content_id' => $content_id],
      ['created_at' => 'ASC']
    );
  }
}
