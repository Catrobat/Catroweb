<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class IfLogicElseStatement extends Statement
{
    const BEGIN_STRING = "else";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces - 1,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getSpacesForNextBrick()
    {
        return $this->spaces + 1;
    }

}

?>

