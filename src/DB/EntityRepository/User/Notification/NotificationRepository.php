<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Notification;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
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
      ->from(LikeNotification::class, 'n')
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
      ->from(FollowNotification::class, 'n')
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
      ->from(CatroNotification::class, 'n')
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

  private function applyTypeFilter(QueryBuilder $qb, string $type): void
  {
    match ($type) {
      'reaction' => $qb->andWhere('n INSTANCE OF '.LikeNotification::class),
      'follow' => $qb->andWhere('(n INSTANCE OF '.FollowNotification::class.' OR n INSTANCE OF '.NewProgramNotification::class.')'),
      'comment' => $qb->andWhere('n INSTANCE OF '.CommentNotification::class),
      'remix' => $qb->andWhere('n INSTANCE OF '.RemixNotification::class),
      default => null,
    };
  }
}
