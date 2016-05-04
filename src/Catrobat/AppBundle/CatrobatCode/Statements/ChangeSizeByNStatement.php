<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ChangeSizeByNStatement extends BaseChangeByNStatement
{
    const BEGIN_STRING = "size";
    const END_STRING = ")%<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
