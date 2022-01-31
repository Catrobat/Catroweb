<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiLoader;
use App\Entity\User;
use App\Manager\UserManager;

final class UserApiLoader extends AbstractApiLoader
{
  private UserManager $user_manager;

  public function __construct(UserManager $user_manager)
  {
    $this->user_manager = $user_manager;
  }

  public function findUserByID(string $id): ?User
  {
    /* @var User|null $user */
    return $this->user_manager->find($id);
  }

  public function searchUsers(string $query, int $limit, int $offset): array
  {
    if ('' === trim($query) || ctype_space($query)) {
      return [];
    }

    return $this->user_manager->search($query, $limit, $offset);
  }
}
