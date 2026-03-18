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
    $followers = $this->queryRelatedUsers($user, 'f.followers');
    $total_following = $this->countRelatedUsers($user, 'f.following');

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
    $following = $this->queryRelatedUsers($user, 'f.following');
    $total_followers = $this->countRelatedUsers($user, 'f.followers');

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
}
