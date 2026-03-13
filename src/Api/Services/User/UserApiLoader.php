<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;

class UserApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly UserManager $user_manager)
  {
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

  public function getAllUsers(string $query, int $limit, int $offset): array
  {
    if ('' === trim($query) || ctype_space($query)) {
      return [];
    }

    return $this->user_manager->findAll();
  }
}
