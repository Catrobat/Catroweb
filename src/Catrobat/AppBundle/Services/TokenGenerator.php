<?php

namespace Catrobat\AppBundle\Services;

/**
 * Class TokenGenerator
 * @package Catrobat\AppBundle\Services
 */
class TokenGenerator
{
  /**
   * TokenGenerator constructor.
   */
  public function __construct()
  {
  }

  /**
   * @return string
   */
  public function generateToken()
  {
    return md5(uniqid(rand(), false));
  }
}
