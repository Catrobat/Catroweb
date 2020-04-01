<?php

namespace App\Catrobat\Services;

use Exception;

class TokenGenerator
{
  /**
   * TokenGenerator constructor.
   *
   * @deprecated use JWT tokens
   */
  public function __construct()
  {
  }

  /**
   * @throws Exception
   */
  public function generateToken(): string
  {
    return md5(uniqid((string) random_int(0, mt_getrandmax()), false));
  }
}
