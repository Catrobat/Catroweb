<?php

namespace Catrobat\AppBundle\Services;

/**
 * Class Time
 * @package Catrobat\AppBundle\Services
 */
class Time
{
  /**
   * @return int
   */
  public function getTime()
  {
    return time();
  }
}
