<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ScriptListStatement extends Statement
{

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces - 1,
            "", "");
    }

    protected function addSpaces($offset = 0)
    {
        return "";
    }
}

?>
