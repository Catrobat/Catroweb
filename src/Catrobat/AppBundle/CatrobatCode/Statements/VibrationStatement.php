<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class VibrationStatement extends Statement
{
    const BEGIN_STRING = "vibrating for ";
    const END_STRING = " seconds<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>

