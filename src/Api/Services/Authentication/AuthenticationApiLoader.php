<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractApiLoader;
use App\User\UserManager;

class AuthenticationApiLoader extends AbstractApiLoader
{
  public function __construct(protected UserManager $user_manager)
  {
  }
}
