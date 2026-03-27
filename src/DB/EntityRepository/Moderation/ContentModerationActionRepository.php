<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Comment\UserComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContentModerationAction>
 */
class ContentModerationActionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ContentModerationAction::class);
  }

  /**
   * @return ContentModerationAction[]
   */
  public function findActionsForContent(string $content_type, string $content_id): array
  {
    return $this->findBy(
      ['content_type' => $content_type, 'content_id' => $content_id],
      ['created_at' => 'DESC']
    );
  }

  /**
   * Counts auto-hide actions targeting a user's content within the last N days.
   * Used to apply a probation penalty to repeat offenders.
   */
  public function countRecentAutoHidesForUser(string $user_id, int $days): int
  {
    $since = new \DateTime()->modify("-{$days} days");

    // Count auto-hide actions on projects owned by this user
    $project_count = (int) $this->getEntityManager()->createQueryBuilder()
      ->select('COUNT(a.id)')
      ->from(ContentModerationAction::class, 'a')
      ->where('a.action = :action')
      ->andWhere('a.content_type = :project_type')
      ->andWhere('a.created_at >= :since')
      ->andWhere('a.content_id IN (SELECT p.id FROM '.Program::class.' p WHERE p.user = :user_id)')
      ->setParameter('action', ContentModerationAction::ACTION_AUTO_HIDDEN)
      ->setParameter('project_type', 'project')
      ->setParameter('since', $since)
      ->setParameter('user_id', $user_id)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    // Count auto-hide actions on comments owned by this user.
    // DQL does not support CAST(... AS string), so we fetch comment ids and compare against string content_id values.
    $comment_id_rows = $this->getEntityManager()->createQueryBuilder()
      ->select('c.id')
      ->from(UserComment::class, 'c')
      ->where('c.user = :user_id')
      ->setParameter('user_id', $user_id)
      ->getQuery()
      ->getScalarResult()
    ;
    $comment_ids = array_map(static fn (array $row): string => (string) $row['id'], $comment_id_rows);

    $comment_count = 0;
    if ([] !== $comment_ids) {
      $comment_count = (int) $this->getEntityManager()->createQueryBuilder()
        ->select('COUNT(a.id)')
        ->from(ContentModerationAction::class, 'a')
        ->where('a.action = :action')
        ->andWhere('a.content_type = :comment_type')
        ->andWhere('a.created_at >= :since')
        ->andWhere('a.content_id IN (:comment_ids)')
        ->setParameter('action', ContentModerationAction::ACTION_AUTO_HIDDEN)
        ->setParameter('comment_type', 'comment')
        ->setParameter('since', $since)
        ->setParameter('comment_ids', $comment_ids)
        ->getQuery()
        ->getSingleScalarResult()
      ;
    }

    // Count auto-hide actions on user profile
    $user_count = (int) $this->getEntityManager()->createQueryBuilder()
      ->select('COUNT(a.id)')
      ->from(ContentModerationAction::class, 'a')
      ->where('a.action = :action')
      ->andWhere('a.content_type = :user_type')
      ->andWhere('a.content_id = :user_id')
      ->andWhere('a.created_at >= :since')
      ->setParameter('action', ContentModerationAction::ACTION_AUTO_HIDDEN)
      ->setParameter('user_type', 'user')
      ->setParameter('user_id', $user_id)
      ->setParameter('since', $since)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return $project_count + $comment_count + $user_count;
  }
}
