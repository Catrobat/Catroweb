<?php

namespace App\System\Testing;

use App\Security\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  /**
   * FixedTokenGenerator constructor.
   *
   * @param mixed $token
   */
  public function __construct(private $token)
  {
  }

  public function generateToken(): string
  {
    return $this->token;
  }
}
