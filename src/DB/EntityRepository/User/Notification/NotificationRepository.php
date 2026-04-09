<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Notification;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\ProjectDeletedNotification;
use App\DB\Entity\User\Notifications\ProjectExpiringNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\Notifications\StudioCommentNotification;
use App\DB\Entity\User\Notifications\StudioJoinRequestNotification;
use App\DB\Entity\User\Notifications\StudioProjectNotification;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CatroNotification>
 */
class NotificationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, CatroNotification::class);
  }

  /**
   * @return LikeNotification[]
   */
  public function getLikeNotificationsForProject(Program $project, ?User $owner = null, ?User $likeFrom = null): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->select('n')
      ->addSelect('nu')
      ->addSelect('lf')
      ->addSelect('p')
      ->from(LikeNotification::class, 'n')
      ->leftJoin('n.user', 'nu')
      ->leftJoin('n.like_from', 'lf')
      ->leftJoin('n.program', 'p')
      ->where($qb->expr()->eq('n.program', ':program_id'))
      ->setParameter(':program_id', $project->getId())
    ;

    if ($owner instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if ($likeFrom instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.like_from', ':like_from_id'))
        ->setParameter(':like_from_id', $likeFrom->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * @return FollowNotification[]
   */
  public function getFollowNotificationForUser(?User $owner = null, ?User $follow_from = null): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->select('n')
      ->addSelect('nu')
      ->addSelect('f')
      ->from(FollowNotification::class, 'n')
      ->leftJoin('n.user', 'nu')
      ->leftJoin('n.follower', 'f')
    ;

    if ($owner instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if ($follow_from instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.follower', ':follower_id'))
        ->setParameter(':follower_id', $follow_from->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }

  public function markAllNotificationsFromUserAsSeen(User $user): void
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->update(CatroNotification::class, 'n')
      ->set('n.seen', ':seen')
      ->where($qb->expr()->eq('n.user', ':user'))
      ->andWhere($qb->expr()->eq('n.seen', ':unseen'))
      ->setParameter('seen', true)
      ->setParameter('user', $user)
      ->setParameter('unseen', false)
      ->getQuery()
      ->execute()
    ;
  }

  /**
   * @return array{notifications: CatroNotification[], has_more: bool}
   */
  public function getNotificationsPageData(User $user, string $type, int $limit, ?int $cursor_id): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->select('n')
      ->addSelect('nu')
      ->from(CatroNotification::class, 'n')
      ->leftJoin('n.user', 'nu')
      ->where('n.user = :user')
      ->setParameter('user', $user)
      ->orderBy('n.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    $this->applyTypeFilter($qb, $type);

    if (null !== $cursor_id) {
      $qb
        ->andWhere('n.id < :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var CatroNotification[] $results */
    $results = $qb->getQuery()->getResult();

    $has_more = count($results) > $limit;
    if ($has_more) {
      array_pop($results);
    }

    return ['notifications' => $results, 'has_more' => $has_more];
  }

  /**
   * @return array{total: int, like: int, follower: int, comment: int, remix: int, moderation: int, studio: int, project: int}
   */
  public function getUnseenCounts(User $user): array
  {
    $conn = $this->getEntityManager()->getConnection();
    $tableName = $this->getClassMetadata()->getTableName();

    $sql = <<<'SQL'
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN notification_type = 'like' THEN 1 ELSE 0 END) AS `like`,
        SUM(CASE WHEN notification_type IN ('follow', 'follow_project') THEN 1 ELSE 0 END) AS follower,
        SUM(CASE WHEN notification_type = 'comment' THEN 1 ELSE 0 END) AS comment,
        SUM(CASE WHEN notification_type = 'remix_notification' THEN 1 ELSE 0 END) AS remix,
        SUM(CASE WHEN notification_type = 'moderation' THEN 1 ELSE 0 END) AS moderation,
        SUM(CASE WHEN notification_type IN ('studio_comment', 'studio_project', 'studio_join_request') THEN 1 ELSE 0 END) AS studio,
        SUM(CASE WHEN notification_type IN ('comment', 'project_expiring', 'project_deleted') THEN 1 ELSE 0 END) AS project
      FROM %s
      WHERE user = :user_id AND seen = 0
      SQL;

    $row = $conn->fetchAssociative(sprintf($sql, $tableName), ['user_id' => $user->getId()]);

    if (false === $row) {
      return ['total' => 0, 'like' => 0, 'follower' => 0, 'comment' => 0, 'remix' => 0, 'moderation' => 0, 'studio' => 0, 'project' => 0];
    }

    return [
      'total' => (int) $row['total'],
      'like' => (int) $row['like'],
      'follower' => (int) $row['follower'],
      'comment' => (int) $row['comment'],
      'remix' => (int) $row['remix'],
      'moderation' => (int) $row['moderation'],
      'studio' => (int) $row['studio'],
      'project' => (int) $row['project'],
    ];
  }

  private function applyTypeFilter(QueryBuilder $qb, string $type): void
  {
    match ($type) {
      'reaction' => $qb->andWhere('n INSTANCE OF '.LikeNotification::class),
      'follow' => $qb->andWhere('(n INSTANCE OF '.FollowNotification::class.' OR n INSTANCE OF '.NewProgramNotification::class.')'),
      'comment' => $qb->andWhere('(n INSTANCE OF '.CommentNotification::class.' OR n INSTANCE OF '.StudioCommentNotification::class.')'),
      'remix' => $qb->andWhere('n INSTANCE OF '.RemixNotification::class),
      'moderation' => $qb->andWhere('n INSTANCE OF '.ModerationNotification::class),
      'studio' => $qb->andWhere('(n INSTANCE OF '.StudioCommentNotification::class.' OR n INSTANCE OF '.StudioProjectNotification::class.' OR n INSTANCE OF '.StudioJoinRequestNotification::class.')'),
      'project' => $qb->andWhere('(n INSTANCE OF '.CommentNotification::class.' OR n INSTANCE OF '.ProjectExpiringNotification::class.' OR n INSTANCE OF '.ProjectDeletedNotification::class.')'),
      default => null,
    };
  }
}
