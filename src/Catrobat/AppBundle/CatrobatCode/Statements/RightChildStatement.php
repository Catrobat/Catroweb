<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class RightChildStatement extends FormulaStatement
{

    const BEGIN_STRING = "";
    const END_STRING = "";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }
}

?>