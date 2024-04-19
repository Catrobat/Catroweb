<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Security\TokenGenerator;

class ProxyTokenGenerator extends TokenGenerator
{
  public function __construct(private TokenGenerator $generator)
  {
  }

  public function generateToken(): string
  {
    return $this->generator->generateToken();
  }

  public function setTokenGenerator(TokenGenerator $generator): void
  {
    $this->generator = $generator;
  }
}
