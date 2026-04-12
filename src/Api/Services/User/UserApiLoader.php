<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;

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

  public function getAllUsers(?string $query, int $limit, int $offset): array
  {
    if (null === $query || '' === trim($query)) {
      return $this->user_manager->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
    }

    return $this->user_manager->search($query, $limit, $offset);
  }

  public function countAllUsers(?string $query): int
  {
    if (null === $query || '' === trim($query)) {
      return count($this->user_manager->findAll());
    }

    return $this->user_manager->searchCount($query);
  }

  /**
   * Keyset cursor query for all users ordered by createdAt DESC, id DESC.
   *
   * @return User[]
   */
  public function getAllUsersKeyset(int $limit, ?\DateTimeInterface $cursor_date = null, ?string $cursor_id = null): array
  {
    $qb = $this->entity_manager->createQueryBuilder()
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
}
