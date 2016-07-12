<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class WaitStatement extends Statement
{
    const BEGIN_STRING = "wait (";
    const END_STRING = ") seconds<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function getBrickText()
    {
        $formula_string = $this->getFormulaListChildStatement()->executeChildren();
        $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

        return "Wait " . $formula_string_without_markup . " seconds";
    }

    public function getBrickColor()
    {
        return "1h_brick_orange.png";
    }

}

?>
