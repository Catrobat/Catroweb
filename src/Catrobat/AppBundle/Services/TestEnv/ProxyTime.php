<?php

namespace Catrobat\AppBundle\Services\TestEnv;

use Catrobat\AppBundle\Services\Time;

/**
 * Class ProxyTime
 * @package Catrobat\AppBundle\Services\TestEnv
 */
class ProxyTime extends Time
{
  /**
   * @var Time
   */
  protected $time;

  /**
   * ProxyTime constructor.
   *
   * @param Time $time
   */
  public function __construct(Time $time)
  {
    $this->time = $time;
  }

  /**
   * @param $time
   */
  public function setTime($time)
  {
    $this->time = $time;
  }

  /**
   * @return int
   */
  public function getTime()
  {
    return $this->time->getTime();
  }
}
