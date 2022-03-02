<?php

namespace App\System\Testing;

use App\Security\TokenGenerator;

class ProxyTokenGenerator extends TokenGenerator
{
  private TokenGenerator $generator;

  public function __construct(TokenGenerator $default_generator)
  {
    $this->generator = $default_generator;
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
