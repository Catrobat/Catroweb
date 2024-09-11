<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Must only be used for internal usage; Use JWT token when possible.
 */
class TokenGenerator
{
  /**
   * @throws \Exception
   */
  public function generateToken(): string
  {
    return md5(uniqid((string) random_int(0, mt_getrandmax())));
  }
}
