<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;

class AuthenticationApiLoader extends AbstractApiLoader
{
  public function __construct(protected UserManager $user_manager)
  {
  }

  public function findUserByUploadToken(string $upload_token): ?User
  {
    return $this->user_manager->findOneBy(['upload_token' => $upload_token]);
  }
}
