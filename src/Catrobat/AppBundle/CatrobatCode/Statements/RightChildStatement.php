<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class RightChildStatement extends FormulaStatement
{

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            "");
    }
}

?>
