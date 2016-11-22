<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class VibrationStatement extends Statement
{
    const BEGIN_STRING = "vibrating for ";
    const END_STRING = " seconds<br/>";

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

        return "Vibrate for " . $formula_string_without_markup ." second(s)";
    }

    public function getBrickColor()
    {
        return "1h_brick_blue.png";
    }

}

?>
