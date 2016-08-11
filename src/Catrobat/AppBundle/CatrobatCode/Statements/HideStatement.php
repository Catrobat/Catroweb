<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class HideStatement extends Statement
{
    const BEGIN_STRING = "hide";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        return "Hide";
    }

    public function getBrickColor()
    {
        return "1h_brick_green.png";
    }
}

?>
