<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class BroadcastMessageStatement extends Statement
{

    const BEGIN_STRING = "";
    const END_STRING = "";

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            self::END_STRING);
    }

}

?>