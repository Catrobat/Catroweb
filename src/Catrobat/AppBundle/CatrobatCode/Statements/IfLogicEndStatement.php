<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicEndStatement extends Statement
{
    const BEGIN_STRING = "end if";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END;

        parent::__construct($statementFactory, $xmlTree, $spaces - 1,
            $stmt,
            self::END_STRING);
    }

}

?>
