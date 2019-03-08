<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\Time;

/**
 * Class ProxyTime
 * @package App\Catrobat\Services\TestEnv
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
