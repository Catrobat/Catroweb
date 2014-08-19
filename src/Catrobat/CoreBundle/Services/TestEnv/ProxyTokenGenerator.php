<?php

namespace Catrobat\CoreBundle\Services\TestEnv;

use Catrobat\CoreBundle\Services\TokenGenerator;

class ProxyTokenGenerator extends TokenGenerator
{
  private $generator;
  
  function __construct(TokenGenerator $default_generator)
  {
    $this->generator = $default_generator;
  }
  
  function generateToken()
  {
    return $this->generator->generateToken();
  }
  
  function setTokenGenerator(TokenGenerator $generator)
  {
    $this->generator = $generator;
  }
}