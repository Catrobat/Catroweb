<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ChangeXByNStatement extends BaseChangeByNStatement
{
    const BEGIN_STRING = "X";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>

