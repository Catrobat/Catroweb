<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class AddItemToUserListStatement extends BaseUserListStatement
{
    const BEGIN_STRING = "add item to userlist ";
    const MIDDLE_STRING = "(";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::MIDDLE_STRING,
            self::END_STRING);
    }
}

?>

