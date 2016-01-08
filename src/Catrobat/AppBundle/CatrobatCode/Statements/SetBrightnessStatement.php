<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class SetBrightnessStatement extends Statement
{
    const BEGIN_STRING = "set brightness to (";
    const END_STRING = ")%<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
