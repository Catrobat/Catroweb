<?php

namespace AppBundle\Features\Api\Context;

use Catrobat\CoreBundle\Services\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  private $token;
  /*
   * (non-PHPdoc) @see \Catrobat\CoreBundle\Services\TokenGenerator::__construct()
   */
  public function __construct($token)
  {
    $this->token = $token;
  }
  
  /*
   * (non-PHPdoc) @see \Catrobat\CoreBundle\Services\TokenGenerator::generateToken()
   */
  public function generateToken()
  {
    return $this->token;
  }

}