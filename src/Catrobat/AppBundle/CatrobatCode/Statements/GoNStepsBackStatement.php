<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class GoNStepsBackStatement extends Statement
{
    const BEGIN_STRING = "go back (";
    const END_STRING = ") layers<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>

