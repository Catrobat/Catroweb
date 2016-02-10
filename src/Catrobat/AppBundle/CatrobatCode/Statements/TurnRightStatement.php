<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class TurnRightStatement extends Statement
{
    const BEGIN_STRING = "turn right (";
    const END_STRING = ") degrees<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
