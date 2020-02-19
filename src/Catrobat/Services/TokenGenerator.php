<?php

namespace App\Catrobat\Services;

/**
 * Class TokenGenerator.
 */
class TokenGenerator
{
  /**
   * TokenGenerator constructor.
   */
  public function __construct()
  {
  }

  /**
   * @return string
   */
  public function generateToken()
  {
    return md5(uniqid(rand(), false));
  }
}
