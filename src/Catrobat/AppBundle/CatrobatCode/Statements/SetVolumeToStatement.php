<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class SetVolumeToStatement extends Statement
{
    const BEGIN_STRING = "set volume to (";
    const END_STRING = ")%<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
