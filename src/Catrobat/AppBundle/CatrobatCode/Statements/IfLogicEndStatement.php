<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class IfLogicEndStatement extends Statement
{
    const BEGIN_STRING = "end if";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces - 1,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>

