<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ShowStatement extends Statement
{
    const BEGIN_STRING = "show";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        return "Show";
    }

    public function getBrickColor()
    {
        return "1h_brick_green.png";
    }

}

?>
