<?php

namespace Catrobat\AppBundle\Features\Api\Context;

use Catrobat\AppBundle\Services\TokenGenerator;

class FixedTokenGenerator extends TokenGenerator
{
  private $token;

  /*
   * (non-PHPdoc) @see \Catrobat\AppBundle\Services\TokenGenerator::__construct()
   */
  public function __construct($token)
  {
    $this->token = $token;
  }

  /*
   * (non-PHPdoc) @see \Catrobat\AppBundle\Services\TokenGenerator::generateToken()
   */
  public function generateToken()
  {
    return $this->token;
  }
}
