<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ShowTextStatement extends Statement
{
    const BEGIN_STRING = "show variable ";
    const END_STRING = ")<br/>";


    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }


}

?>
