<?php

namespace Catrobat\AppBundle\Services\TestEnv;

use Catrobat\AppBundle\Services\TokenGenerator;

class ProxyTokenGenerator extends TokenGenerator
{
  private $generator;

  public function __construct(TokenGenerator $default_generator)
  {
    $this->generator = $default_generator;
  }

  public function generateToken()
  {
    return $this->generator->generateToken();
  }

  public function setTokenGenerator(TokenGenerator $generator)
  {
    $this->generator = $generator;
  }
}
