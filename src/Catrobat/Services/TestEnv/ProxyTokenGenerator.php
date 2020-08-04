<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\TokenGenerator;

/**
 * TokenGenerator constructor.
 *
 * @deprecated use JWT tokens
 */
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
