<?php

namespace App\Catrobat\Services;

use Exception;

/**
 * @deprecated use JWT tokens
 */
class TokenGenerator
{
  /**
   * @throws Exception
   */
  public function generateToken(): string
  {
    return md5(uniqid((string) random_int(0, mt_getrandmax()), false));
  }
}
