<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class UserListStatement extends Statement
{

    const BEGIN_STRING = "";

    public function __construct($statementFactory, $xmlTree, $spaces, $value)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            "");
    }

}

?>