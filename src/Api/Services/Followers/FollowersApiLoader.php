<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class FollowersApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  /**
   * @return array{users: User[], total_followers: int, total_following: int}
   */
  public function getFollowers(User $user): array
  {
    $followers = $this->queryRelatedUsers($user, 'f.following');
    $total_following = $this->countRelatedUsers($user, 'f.followers');

    return [
      'users' => $followers,
      'total_followers' => count($followers),
      'total_following' => $total_following,
    ];
  }

  /**
   * @return array{users: User[], total_followers: int, total_following: int}
   */
  public function getFollowing(User $user): array
  {
    $following = $this->queryRelatedUsers($user, 'f.followers');
    $total_followers = $this->countRelatedUsers($user, 'f.following');

    return [
      'users' => $following,
      'total_followers' => $total_followers,
      'total_following' => count($following),
    ];
  }

  /**
   * @return User[]
   */
  private function queryRelatedUsers(User $user, string $joinRelation): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('f')
      ->from(User::class, 'f')
      ->join($joinRelation, 'u')
      ->where('u.id = :userId')
      ->andWhere('f.profile_hidden = false')
      ->setParameter('userId', $user->getId())
      ->orderBy('f.username', 'ASC')
    ;

    return $qb->getQuery()->getResult();
  }

  private function countRelatedUsers(User $user, string $joinRelation): int
  {
    return (int) $this->entity_manager->createQueryBuilder()
      ->select('COUNT(f.id)')
      ->from(User::class, 'f')
      ->join($joinRelation, 'u')
      ->where('u.id = :userId')
      ->andWhere('f.profile_hidden = false')
      ->setParameter('userId', $user->getId())
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }

  /**
   * Returns the set of user IDs (from $user_ids) that $user is following.
   *
   * @param string[] $user_ids
   *
   * @return array<string, true> user_id => true for each followed user
   */
  public function getFollowedUserIds(User $user, array $user_ids): array
  {
    if ([] === $user_ids) {
      return [];
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $rows = $qb->select('f.id AS followed_id')
      ->from(User::class, 'u')
      ->join('u.following', 'f')
      ->where('u.id = :userId')
      ->andWhere('f.id IN (:targetIds)')
      ->setParameter('userId', $user->getId())
      ->setParameter('targetIds', $user_ids)
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[(string) $row['followed_id']] = true;
    }

    return $map;
  }

  /**
   * Returns the set of user IDs (from $user_ids) that follow $user.
   *
   * @param string[] $user_ids
   *
   * @return array<string, true> user_id => true for each user that follows $user
   */
  public function getFollowerOfUserIds(User $user, array $user_ids): array
  {
    if ([] === $user_ids) {
      return [];
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $rows = $qb->select('f.id AS follower_id')
      ->from(User::class, 'f')
      ->join('f.following', 'u')
      ->where('u.id = :userId')
      ->andWhere('f.id IN (:targetIds)')
      ->setParameter('userId', $user->getId())
      ->setParameter('targetIds', $user_ids)
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[(string) $row['follower_id']] = true;
    }

    return $map;
  }

  /**
   * Batch-count followers for multiple users in a single query.
   *
   * @param string[] $user_ids
   *
   * @return array<string, int> user_id => follower count
   */
  public function getFollowerCountsForUsers(array $user_ids): array
  {
    if ([] === $user_ids) {
      return [];
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $rows = $qb->select('u.id AS user_id, COUNT(f.id) AS cnt')
      ->from(User::class, 'u')
      ->leftJoin('u.followers', 'f')
      ->where('u.id IN (:userIds)')
      ->setParameter('userIds', $user_ids)
      ->groupBy('u.id')
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[(string) $row['user_id']] = (int) $row['cnt'];
    }

    return $map;
  }

  /**
   * Batch-count public projects for multiple users in a single query.
   *
   * @param string[] $user_ids
   *
   * @return array<string, int> user_id => project count
   */
  public function getProjectCountsForUsers(array $user_ids): array
  {
    if ([] === $user_ids) {
      return [];
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $rows = $qb->select('IDENTITY(p.user) AS user_id, COUNT(p.id) AS cnt')
      ->from(\App\DB\Entity\Project\Project::class, 'p')
      ->where('p.user IN (:userIds)')
      ->andWhere('p.visible = true')
      ->andWhere('p.auto_hidden = false')
      ->andWhere('p.private = false')
      ->andWhere('p.debug_build = false')
      ->setParameter('userIds', $user_ids)
      ->groupBy('p.user')
      ->getQuery()
      ->getArrayResult()
    ;

    $map = [];
    foreach ($rows as $row) {
      $map[(string) $row['user_id']] = (int) $row['cnt'];
    }

    return $map;
  }
}
