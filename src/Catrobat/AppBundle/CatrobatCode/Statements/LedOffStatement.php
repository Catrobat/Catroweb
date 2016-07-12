<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class LedOffStatement extends Statement
{
    const BEGIN_STRING = "led off";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        return "Turn flashlight off";
    }

    public function getBrickColor()
    {
        return "1h_brick_green.png";
    }
}

?>
