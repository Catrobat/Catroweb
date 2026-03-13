<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Moderation;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Enum\AppealState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContentAppeal>
 */
class ContentAppealRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ContentAppeal::class);
  }

  public function hasExistingAppeal(string $content_type, string $content_id, string $user_id): bool
  {
    $qb = $this->createQueryBuilder('a');

    return (int) $qb
      ->select('COUNT(a.id)')
      ->join('a.appellant', 'u')
      ->where('a.content_type = :content_type')
      ->andWhere('a.content_id = :content_id')
      ->andWhere('u.id = :user_id')
      ->andWhere('a.state = :pending')
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->setParameter('user_id', $user_id)
      ->setParameter('pending', AppealState::Pending->value)
      ->getQuery()
      ->getSingleScalarResult() > 0
    ;
  }

  /**
   * Removes resolved (approved/rejected) appeals for the same content+user,
   * making room in the unique constraint for a new appeal.
   */
  public function removeResolvedAppeals(string $content_type, string $content_id, string $user_id): void
  {
    $this->createQueryBuilder('a')
      ->delete()
      ->where('a.content_type = :content_type')
      ->andWhere('a.content_id = :content_id')
      ->andWhere('a.state != :pending')
      ->andWhere('a.appellant = :user_id')
      ->setParameter('content_type', $content_type)
      ->setParameter('content_id', $content_id)
      ->setParameter('pending', AppealState::Pending->value)
      ->setParameter('user_id', $user_id)
      ->getQuery()
      ->execute()
    ;
  }

  /**
   * @return ContentAppeal[]
   */
  public function findPendingAppeals(
    int $limit,
    ?\DateTimeInterface $cursor_created_at = null,
    ?int $cursor_id = null,
    ?int $legacy_cursor_id = null,
  ): array {
    $qb = $this->createQueryBuilder('a');
    $qb
      ->where('a.state = :state')
      ->setParameter('state', AppealState::Pending->value)
      ->orderBy('a.created_at', 'ASC')
      ->addOrderBy('a.id', 'ASC')
      ->setMaxResults($limit + 1)
    ;

    if ($cursor_created_at instanceof \DateTimeInterface && null !== $cursor_id) {
      $qb->andWhere(
        $qb->expr()->orX(
          $qb->expr()->gt('a.created_at', ':cursor_created_at'),
          $qb->expr()->andX(
            $qb->expr()->eq('a.created_at', ':cursor_created_at'),
            $qb->expr()->gt('a.id', ':cursor_id')
          )
        )
      )
        ->setParameter('cursor_created_at', $cursor_created_at)
        ->setParameter('cursor_id', $cursor_id)
      ;
    } elseif (null !== $legacy_cursor_id) {
      $qb
        ->andWhere('a.id > :legacy_cursor_id')
        ->setParameter('legacy_cursor_id', $legacy_cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }
}
