<?php
/**
 * Created by IntelliJ IDEA.
 * User: catroweb
 * Date: 21.08.14
 * Time: 16:18
 */

namespace AppBundle\Services\TestEnv;


use AppBundle\Services\Time;

class ProxyTime extends Time
{
  protected $time;

  function __construct(Time $time)
  {
    $this->time = $time;
  }

  function setTime($time)
  {
    $this->time = $time;
  }

  function getTime()
  {
    return $this->time->getTime();
  }


} 