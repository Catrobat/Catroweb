<?php

namespace AppBundle\Features\Api\Context;

use AppBundle\Services\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  private $token;
  /*
   * (non-PHPdoc) @see \AppBundle\Services\TokenGenerator::__construct()
   */
  public function __construct($token)
  {
    $this->token = $token;
  }
  
  /*
   * (non-PHPdoc) @see \AppBundle\Services\TokenGenerator::generateToken()
   */
  public function generateToken()
  {
    return $this->token;
  }

}