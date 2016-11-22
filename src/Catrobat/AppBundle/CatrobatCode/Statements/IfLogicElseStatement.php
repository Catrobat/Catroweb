<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicElseStatement extends Statement
{
    const BEGIN_STRING = "else";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END;

        parent::__construct($statementFactory, $xmlTree, $spaces - 1,
            $stmt,
            self::END_STRING);
    }

    public function getSpacesForNextBrick()
    {
        return $this->spaces + 1;
    }

    public function getBrickText()
    {
        return "Else";
    }

    public function getBrickColor()
    {
        return "1h_brick_orange.png";
    }
}

?>
