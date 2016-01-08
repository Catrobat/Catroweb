<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\StatementFactory;

class Statement
{
    protected $xmlTree;
    protected $statements;
    protected $spaces;
    private $beginString;
    private $endString;

    public function __construct(StatementFactory $statementFactory, $xmlTree, $spaces, $beginString, $endString)
    {
        $this->statements = array();
        $this->xmlTree = $xmlTree;
        $this->beginString = $beginString;
        $this->endString = $endString;
        $this->spaces = $spaces;

        $this->createChildren($statementFactory);
    }

    protected function createChildren(StatementFactory $statementFactory)
    {
        if ($this->xmlTree != null) {
            $this->addAllScripts($statementFactory->createStatement($this->xmlTree, $this->spaces + 1));
        }
    }

    protected function addAllScripts($statementsToAdd)
    {
        foreach ($statementsToAdd as $statement) {
            $this->statements[] = $statement;
        }
    }

    public function execute()
    {
        $code = $this->addSpaces() . $this->beginString . $this->executeChildren() . $this->endString;
        return $code;
    }

    protected function addSpaces($offset = 0)
    {
        $stringSpaces = "";
        for ($i = 0; $i < ($this->spaces + $offset) * 4; $i++) {
            $stringSpaces .= "&nbsp;";
        }
        return $stringSpaces;
    }

    public function executeChildren()
    {
        $code = '';

        foreach ($this->statements as $value) {
            $code .= $value->execute();
        }

        return $code;
    }

    public function getSpacesForNextBrick()
    {
        return $this->spaces;
    }

    public function getStatements()
    {
        return $this->statements;
    }

    public function getBeginString()
    {
        return $this->beginString;
    }

    public function getEndString()
    {
        return $this->endString;
    }
}

?>
