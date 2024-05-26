<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Security\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  public function __construct(private readonly mixed $token)
  {
  }

  #[\Override]
  public function generateToken(): string
  {
    return $this->token;
  }
}
