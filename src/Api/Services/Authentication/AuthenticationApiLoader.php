<?php

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;

final class AuthenticationApiLoader extends AbstractApiLoader
{
  protected UserManager $user_manager;

  public function __construct(UserManager $user_manager)
  {
    $this->user_manager = $user_manager;
  }

  public function findUserByUploadToken(string $upload_token): ?User
  {
    return $this->user_manager->findOneBy(['upload_token' => $upload_token]);
  }
}
