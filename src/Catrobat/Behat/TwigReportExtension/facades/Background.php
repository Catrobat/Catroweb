<?php

namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterBackgroundTested;

class Background
{

  private $event;

  private $steps;

  public function __construct(AfterBackgroundTested $event, $steps)
  {
    $this->event = $event;
    $num_steps = count($event->getBackground()->getSteps());
    $this->steps = array_slice($steps, -$num_steps);
  }

  public function getTitle()
  {
    $this->event->getBackground()->getTitle();
  }

  public function getSteps()
  {
    return $this->steps;
  }

  public function getResult()
  {
    return $this->event->getTestResult()->getResultCode();
  }
}