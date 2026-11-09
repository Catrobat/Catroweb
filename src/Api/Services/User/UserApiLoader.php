<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class UserApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function findUserByID(string $id): ?User
  {
    /* @var User|null $user */
    return $this->user_manager->find($id);
  }

  public function canAccessProfile(User $user, ?User $viewer): bool
  {
    if (!$user->getProfileHidden()) {
      return true;
    }

    if (!$viewer instanceof User) {
      return false;
    }

    if ($viewer->getId() === $user->getId()) {
      return true;
    }
    if ($viewer->hasRole('ROLE_ADMIN')) {
      return true;
    }

    return $viewer->hasRole('ROLE_SUPER_ADMIN');
  }

  public function searchUsers(string $query, int $limit, int $offset): array
  {
    if ('' === trim($query) || ctype_space($query)) {
      return [];
    }

    return $this->user_manager->search($query, $limit, $offset);
  }

  /** @return User[] search results; the list without a query is getAllUsersKeyset() */
  public function getAllUsers(string $query, int $limit, int $offset): array
  {
    return $this->user_manager->search($query, $limit, $offset);
  }

  /**
   * Leaderboard: users with a positive score, highest first. Suspended (profile hidden) and disabled users
   * and users whose parental consent is pending or revoked are excluded at read time, so a suspension
   * takes effect before the next nightly recompute.
   *
   * @return User[]
   */
  public function getRankedUsers(bool $recent, int $limit, int $offset): array
  {
    $score = $recent ? 'u.ranking_score_recent' : 'u.ranking_score';

    return $this->onlyPubliclyVisible($this->entity_manager->createQueryBuilder())
      ->select('u')
      ->from(User::class, 'u')
      ->andWhere($score.' > 0')
      ->orderBy($score, 'DESC')
      // id DESC: InnoDB secondary indexes carry the primary key, so (score DESC, id DESC) is a backward scan
      // of the score index that stops after $limit rows instead of a filesort over every ranked user.
      ->addOrderBy('u.id', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * Keyset cursor query for all users ordered by createdAt DESC, id DESC.
   *
   * @return User[]
   */
  public function getAllUsersKeyset(int $limit, ?\DateTimeInterface $cursor_date = null, ?string $cursor_id = null): array
  {
    $qb = $this->onlyPubliclyVisible($this->entity_manager->createQueryBuilder())
      ->select('u')
      ->from(User::class, 'u')
      ->orderBy('u.createdAt', 'DESC')
      ->addOrderBy('u.id', 'DESC')
      ->setMaxResults($limit)
    ;

    if (null !== $cursor_date && null !== $cursor_id) {
      $qb->andWhere(
        '(u.createdAt < :cursor_date) OR (u.createdAt = :cursor_date AND u.id < :cursor_id)'
      )
        ->setParameter('cursor_date', $cursor_date)
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * Suspended (profile hidden), disabled (incl. Scratch placeholder accounts) and consent-pending or
   * revoked accounts 404 on their own profile, so user lists leave them out too.
   */
  private function onlyPubliclyVisible(QueryBuilder $qb): QueryBuilder
  {
    return $qb
      ->andWhere('u.enabled = true')
      ->andWhere('u.profile_hidden = false')
      ->andWhere('u.consent_status NOT IN (:blocked_consent)')
      ->setParameter('blocked_consent', ['pending', 'revoked'])
    ;
  }
}
