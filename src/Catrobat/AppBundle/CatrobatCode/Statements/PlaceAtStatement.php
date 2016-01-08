<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class PlaceAtStatement extends Statement
{
    const BEGIN_STRING = "place at ";
    const END_STRING = "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

    public function executeChildren()
    {
        $code = '';

        foreach ($this->statements as $value) {
            if ($value instanceof FormulaListStatement) {
                $code .= $value->executePlaceAtFormula();
            }
        }

        return $code;
    }
}

?>

