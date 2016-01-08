<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class IfLogicBeginStatement extends Statement
{
    const BEGIN_STRING = "if (";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getSpacesForNextBrick()
    {
        return $this->spaces + 1;
    }

}

?>

