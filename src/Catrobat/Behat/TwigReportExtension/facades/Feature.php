<?php

namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;

class Feature
{

  private $event;

  private $scenarios;

  private $background;

  private $file;

  public function __construct(AfterFeatureTested $event, $scenarios, $background)
  {
    $this->event = $event;
    $this->scenarios = $scenarios;
    $this->background = $background;
  }

  public function getTitle()
  {
    return $this->event->getFeature()->getTitle();
  }

  public function getDescription()
  {
    return $this->event->getFeature()->getDescription();
  }

  public function getResult()
  {
    return $this->event->getTestResult()->getResultCode();
  }

  public function getScenarios()
  {
    return $this->scenarios;
  }

  public function getBackground()
  {
    return $this->background;
  }

  public function getFile()
  {
    return $this->event->getFeature()->getFile();
  }
}