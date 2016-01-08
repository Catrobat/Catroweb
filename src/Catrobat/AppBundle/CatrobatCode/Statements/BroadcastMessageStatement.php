<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class BroadcastMessageStatement extends Statement
{

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value, "");
    }

}

?>

