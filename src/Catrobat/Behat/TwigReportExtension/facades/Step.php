<?php

namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Testwork\Tester\Result\ExceptionResult;

/**
 * Class Step
 * @package Catrobat\Behat\TwigReportExtension\facades
 */
class Step implements StepInterface
{

  /**
   * @var AfterStepTested
   */
  private $event;

  /**
   * Step constructor.
   *
   * @param AfterStepTested $event
   */
  public function __construct(AfterStepTested $event)
  {
    $this->event = $event;
  }

  /**
   * @return mixed|string
   */
  public function getText()
  {
    return $this->event->getStep()->getKeyword() . " " . $this->event->getStep()->getText();
  }

  /**
   * @return int|mixed
   */
  public function getResult()
  {
    return $this->event->getTestResult()->getResultCode();
  }

  /**
   * @return bool
   */
  private function hasException()
  {
    return $this->event->getTestResult() instanceof ExceptionResult && $this->event->getTestResult()->getException();
  }

  /**
   * @return mixed
   */
  public function getException()
  {
    if ($this->hasException())
    {
      return $this->event->getTestResult()->getException();
    }
  }

  /**
   * @return array|mixed
   */
  public function getArguments()
  {
    $arguments = [];

    foreach ($this->event->getStep()->getArguments() as $argument)
    {
      $argument_array = [];
      $argument_array["type"] = $argument->getNodeType();
      switch ($argument->getNodeType())
      {
        case "PyString":
          $argument_array["text"] = $argument->getRaw();
          break;
        case "Table":
          $argument_array["table"] = $argument->getTable();
          break;
      }
      $arguments[] = $argument_array;
    }

    return $arguments;
  }

  /**
   * @return int|mixed
   */
  public function getLine()
  {
    return $this->event->getStep()->getLine();
  }
}