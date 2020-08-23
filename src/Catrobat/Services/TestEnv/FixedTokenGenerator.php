<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  private string $token;

  /**
   * FixedTokenGenerator constructor.
   *
   * @param mixed $token
   */
  public function __construct($token)
  {
    $this->token = $token;
  }

  public function generateToken(): string
  {
    return $this->token;
  }
}
