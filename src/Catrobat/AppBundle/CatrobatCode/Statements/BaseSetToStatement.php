<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class BaseSetToStatement extends Statement
{

    public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
    {
        $beginString = "set " . SyntaxHighlightingConstants::VARIABLES . $beginString . SyntaxHighlightingConstants::END . " to (";
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $beginString,
            $endString);
    }
}

?>
