<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

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
        $arguments = array();
        
        foreach ($this->stepnode->getArguments() as $argument) {
            $argument_array = array();
            $argument_array["type"] = $argument->getNodeType();
            switch ($argument->getNodeType()) {
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

    public function getLine()
    {
        return $this->stepnode->getLine();
    }
}