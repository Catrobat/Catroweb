<?php

/**
 * Created by IntelliJ IDEA.
 * User: catroweb
 * Date: 21.08.14
 * Time: 16:18.
 */

namespace Catrobat\AppBundle\Services\TestEnv;

use Catrobat\AppBundle\Services\Time;

class ProxyTime extends Time
{
  protected $time;

  public function __construct(Time $time)
  {
    $this->time = $time;
  }

  public function setTime($time)
  {
    $this->time = $time;
  }

  public function getTime()
  {
    return $this->time->getTime();
  }
}
