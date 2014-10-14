<?php
/**
 * Created by IntelliJ IDEA.
 * User: catroweb
 * Date: 21.08.14
 * Time: 16:44
 */

namespace AppBundle\Features\Api\Context;


use AppBundle\Services\Time;

class FixedTime extends Time
{
  protected $timestamp;

  function __construct($timestamp)
  {
    $this->timestamp = $timestamp;
  }

  function getTime()
  {
    return $this->timestamp;
  }
} 