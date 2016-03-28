<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ChangeYByNStatement extends BaseChangeByNStatement
{
    const BEGIN_STRING = "Y";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {

        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>

