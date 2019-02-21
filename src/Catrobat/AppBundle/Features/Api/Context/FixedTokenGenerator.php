<?php

namespace Catrobat\AppBundle\Features\Api\Context;

use Catrobat\AppBundle\Services\TokenGenerator;

/**
 * Class FixedTokenGenerator
 * @package Catrobat\AppBundle\Features\Api\Context
 */
class FixedTokenGenerator extends TokenGenerator
{
  /**
   * @var
   */
  private $token;


  /**
   * FixedTokenGenerator constructor.
   *
   * @param $token
   */
  public function __construct($token)
  {
    parent::__construct();
    $this->token = $token;
  }


  /**
   * @return string
   */
  public function generateToken()
  {
    return $this->token;
  }
}
