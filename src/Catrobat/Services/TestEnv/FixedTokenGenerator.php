<?php

namespace App\Catrobat\Services\TestEnv;


use App\Catrobat\Services\TokenGenerator;


/**
 * Class FixedTokenGenerator
 * @package App\Catrobat\Features\Api\Context
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
