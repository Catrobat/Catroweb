<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class TurnLeftStatement extends Statement
{
    const BEGIN_STRING = "turn left (";
    const END_STRING = ") degrees<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
