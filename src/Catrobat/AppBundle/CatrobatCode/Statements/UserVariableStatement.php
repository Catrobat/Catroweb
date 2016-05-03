<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class UserVariableStatement extends Statement
{

    const BEGIN_STRING = "";

    const AT_END_STRING = " at (";
    const TO_END_STRING = " to (";

    public function __construct($statementFactory, $xmlTree, $spaces, $value, $useAt = false)
    {
        $end = self::TO_END_STRING;
        if ($useAt) {
            $end = self::AT_END_STRING;
        }

        $value = SyntaxHighlightingConstants::VARIABLES . $value . SyntaxHighlightingConstants::END;
        parent::__construct($statementFactory, $xmlTree, $spaces,
            $value,
            $end);
    }

}

?>
