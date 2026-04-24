<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Notification;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\Notifications\NewProjectNotification;
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
  public function getLikeNotificationsForProject(Project $project, ?User $owner = null, ?User $likeFrom = null): array
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
      ->leftJoin('n.project', 'p')
      ->where($qb->expr()->eq('n.project', ':program_id'))
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
  public function getNotificationsPageData(User $user, string $type, int $limit, ?string $cursor_id): array
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

    $this->preloadNotificationRelations($results);

    return ['notifications' => $results, 'has_more' => $has_more];
  }

  /**
   * Batch-preload related entities (users, projects, comments, studios) into Doctrine's
   * identity map so that accessing notification relations doesn't trigger N+1 queries.
   *
   * @param CatroNotification[] $notifications
   */
  private function preloadNotificationRelations(array $notifications): void
  {
    if ([] === $notifications) {
      return;
    }

    $user_ids = [];
    $project_ids = [];
    $comment_ids = [];
    $studio_ids = [];

    foreach ($notifications as $notification) {
      if ($notification instanceof LikeNotification) {
        $user_ids[] = $notification->getLikeFrom()?->getId();
        $project_ids[] = $notification->getProject()?->getId();
      } elseif ($notification instanceof FollowNotification) {
        $user_ids[] = $notification->getFollower()->getId();
      } elseif ($notification instanceof NewProjectNotification) {
        $project_ids[] = $notification->getProject()?->getId();
      } elseif ($notification instanceof CommentNotification) {
        $comment_ids[] = $notification->getComment()?->getId();
      } elseif ($notification instanceof RemixNotification) {
        $user_ids[] = $notification->getRemixFrom()?->getId();
        $project_ids[] = $notification->getProject()?->getId();
        $project_ids[] = $notification->getRemixProgram()?->getId();
      } elseif ($notification instanceof StudioCommentNotification) {
        $user_ids[] = $notification->getCommentUser()?->getId();
        $studio_ids[] = $notification->getStudio()?->getId();
      } elseif ($notification instanceof StudioProjectNotification) {
        $user_ids[] = $notification->getProjectUser()?->getId();
        $project_ids[] = $notification->getProject()?->getId();
        $studio_ids[] = $notification->getStudio()?->getId();
      } elseif ($notification instanceof StudioJoinRequestNotification) {
        $user_ids[] = $notification->getAdminUser()?->getId();
        $studio_ids[] = $notification->getStudio()?->getId();
      } elseif ($notification instanceof ProjectExpiringNotification) {
        $project_ids[] = $notification->getProject()?->getId();
      }
    }

    $em = $this->getEntityManager();

    // Batch-load users into identity map
    $user_ids = array_values(array_unique(array_filter($user_ids)));
    if ([] !== $user_ids) {
      $em->createQueryBuilder()
        ->select('u')
        ->from(User::class, 'u')
        ->where('u.id IN (:ids)')
        ->setParameter('ids', $user_ids)
        ->getQuery()
        ->getResult()
      ;
    }

    // Batch-load projects (with user for getUser() calls)
    $project_ids = array_values(array_unique(array_filter($project_ids)));
    if ([] !== $project_ids) {
      $em->createQueryBuilder()
        ->select('p, pu')
        ->from(Project::class, 'p')
        ->leftJoin('p.user', 'pu')
        ->where('p.id IN (:ids)')
        ->setParameter('ids', $project_ids)
        ->getQuery()
        ->getResult()
      ;
    }

    // Batch-load comments (with user and project)
    $comment_ids = array_values(array_unique(array_filter($comment_ids)));
    if ([] !== $comment_ids) {
      $em->createQueryBuilder()
        ->select('c, cu, cp')
        ->from(UserComment::class, 'c')
        ->leftJoin('c.user', 'cu')
        ->leftJoin('c.project', 'cp')
        ->where('c.id IN (:ids)')
        ->setParameter('ids', $comment_ids)
        ->getQuery()
        ->getResult()
      ;
    }

    // Batch-load studios
    $studio_ids = array_values(array_unique(array_filter($studio_ids)));
    if ([] !== $studio_ids) {
      $em->createQueryBuilder()
        ->select('s')
        ->from(Studio::class, 's')
        ->where('s.id IN (:ids)')
        ->setParameter('ids', $studio_ids)
        ->getQuery()
        ->getResult()
      ;
    }
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
      'follow' => $qb->andWhere('(n INSTANCE OF '.FollowNotification::class.' OR n INSTANCE OF '.NewProjectNotification::class.')'),
      'comment' => $qb->andWhere('(n INSTANCE OF '.CommentNotification::class.' OR n INSTANCE OF '.StudioCommentNotification::class.')'),
      'remix' => $qb->andWhere('n INSTANCE OF '.RemixNotification::class),
      'moderation' => $qb->andWhere('n INSTANCE OF '.ModerationNotification::class),
      'studio' => $qb->andWhere('(n INSTANCE OF '.StudioCommentNotification::class.' OR n INSTANCE OF '.StudioProjectNotification::class.' OR n INSTANCE OF '.StudioJoinRequestNotification::class.')'),
      'project' => $qb->andWhere('(n INSTANCE OF '.CommentNotification::class.' OR n INSTANCE OF '.ProjectExpiringNotification::class.' OR n INSTANCE OF '.ProjectDeletedNotification::class.')'),
      default => null,
    };
  }
}
