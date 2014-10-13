<?php

namespace Catrobat\AppBundle\Services\TestEnv;

use Catrobat\AppBundle\Services\TokenGenerator;

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