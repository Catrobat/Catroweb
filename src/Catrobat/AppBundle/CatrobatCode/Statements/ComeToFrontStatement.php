<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ComeToFrontStatement extends Statement
{
    const BEGIN_STRING = "come to front";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        return "Go to front";
    }

    public function getBrickColor()
    {
        return "1h_brick_blue.png";
    }
}

?>
