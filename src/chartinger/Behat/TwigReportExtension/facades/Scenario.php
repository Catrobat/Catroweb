<?php
namespace chartinger\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;

class Scenario implements ScenarioInterface
{

    private $event;

    private $steps;

    public function __construct(AfterScenarioTested $event, $steps)
    {
        $this->event = $event;
        $this->steps = $steps;
    }

    public function getTitle()
    {
        return $this->event->getScenario()->getTitle();
    }

    public function getResult()
    {
        return $this->event->getTestResult()->getResultCode();
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function isOutline()
    {
        return false;
    }

    public function getParameters()
    {
        return array();
    }

    public function getExamples()
    {
        return array();
    }
}