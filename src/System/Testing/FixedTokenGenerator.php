<?php

namespace App\System\Testing;

use App\Security\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  /**
   * FixedTokenGenerator constructor.
   */
  public function __construct(private readonly mixed $token)
  {
  }

  public function generateToken(): string
  {
    return $this->token;
  }
}
