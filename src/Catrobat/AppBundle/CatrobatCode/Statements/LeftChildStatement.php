<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class LeftChildStatement extends FormulaStatement
{

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            "");
    }

}

?>
