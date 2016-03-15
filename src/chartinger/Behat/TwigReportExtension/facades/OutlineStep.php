<?php
namespace chartinger\Behat\TwigReportExtension\facades;

use Behat\Gherkin\Node\StepNode;

class OutlineStep implements StepInterface
{

    private $stepnode;

    public function __construct(StepNode $step)
    {
        $this->stepnode = $step;
    }

    public function getText()
    {
        return $this->stepnode->getKeyword() . " " . $this->stepnode->getText();
    }

    public function getResult()
    {
        return - 1;
    }

    public function getArguments()
    {
        return $this->stepnode->getArguments();
    }

    public function getLine()
    {
        return $this->stepnode->getLine();
    }
}