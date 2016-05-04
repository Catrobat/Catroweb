<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class BaseChangeByNStatement extends Statement
{
    public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
    {
        $beginString = "change " . SyntaxHighlightingConstants::VARIABLES . $beginString . SyntaxHighlightingConstants::END . " by (";
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $beginString,
            $endString);
    }
}

?>
