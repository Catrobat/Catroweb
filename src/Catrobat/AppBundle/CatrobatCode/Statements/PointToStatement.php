<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class PointToStatement extends Statement
{
    const BEGIN_STRING = "point to ";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        return "Point towards " . $this->xmlTree->pointedObject['name'];
    }

    public function getBrickColor()
    {
        return "1h_brick_blue.png";
    }

}

?>
