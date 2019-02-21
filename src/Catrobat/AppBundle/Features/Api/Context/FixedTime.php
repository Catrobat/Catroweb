<?php

namespace Catrobat\AppBundle\Features\Api\Context;

use Catrobat\AppBundle\Services\Time;

/**
 * Class FixedTime
 * @package Catrobat\AppBundle\Features\Api\Context
 */
class FixedTime extends Time
{
  /**
   * @var
   */
  protected $timestamp;

  /**
   * FixedTime constructor.
   *
   * @param $timestamp
   */
  public function __construct($timestamp)
  {
    $this->timestamp = $timestamp;
  }

  /**
   * @return int
   */
  public function getTime()
  {
    return $this->timestamp;
  }
}
