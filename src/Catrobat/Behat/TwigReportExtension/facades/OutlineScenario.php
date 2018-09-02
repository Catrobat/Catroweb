<?php

namespace Catrobat\Behat\TwigReportExtension\facades;

use Catrobat\Behat\TwigReportExtension\facades\ScenarioInterface;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;

class OutlineScenario implements ScenarioInterface
{

  private $event;

  private $parameters;

  private $examples;

  private $steps;

  public function __construct(AfterOutlineTested $event, $steps)
  {
    $this->event = $event;
    if (count($event->getOutline()
        ->getExampleTable()
        ->getTable()) > 0)
    {
      $this->parameters = array_values($event->getOutline()
        ->getExampleTable()
        ->getTable())[0];
    }
    else
    {
      $this->parameters = [];
    }
    $this->generateExamples($steps);
    $this->generateSteps();
  }

  public function getSteps()
  {
    return $this->steps;
  }

  public function isOutline()
  {
    return true;
  }

  public function getTitle()
  {
    return $this->event->getOutline()->getTitle();
  }

  public function getResult()
  {
    return $this->event->getTestResult()->getResultCode();
  }

  public function getParameters()
  {
    return $this->parameters;
  }

  public function getExamples()
  {
    return $this->examples;
  }

  private function generateExamples($steps)
  {
    $example_nodes = $this->event->getOutline()->getExamples();
    $example_value_table = $this->event->getOutline()->getExampleTable();

    $step_counter = 0;
    $example_counter = 0;

    $example_results = [];

    foreach ($example_nodes as $example_node)
    {
      $worst_result_from_steps = 0;
      $example_steps = [];

      $steps_per_example = count($example_node->getSteps());
      for ($example_step_index = 0; $example_step_index < $steps_per_example; $example_step_index++)
      {
        if ($steps[$step_counter]->getResult() > $worst_result_from_steps)
        {
          $worst_result_from_steps = $steps[$step_counter]->getResult();
        }
        $example_steps[] = $steps[$step_counter];
        $step_counter++;
      }
      $example_results[] = new Example($worst_result_from_steps, $example_value_table->getRow($example_counter + 1), $example_steps);
      $example_counter++;
    }

    $this->examples = $example_results;
  }

  private function generateSteps()
  {
    $this->steps = [];
    foreach ($this->event->getOutline()->getSteps() as $stepnode)
    {
      $this->steps[] = new OutlineStep($stepnode);
    }
  }
}