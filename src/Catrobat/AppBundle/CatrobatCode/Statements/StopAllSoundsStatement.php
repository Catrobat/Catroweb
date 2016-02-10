<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class StopAllSoundsStatement extends Statement
{
    const BEGIN_STRING = "stop all sounds";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
