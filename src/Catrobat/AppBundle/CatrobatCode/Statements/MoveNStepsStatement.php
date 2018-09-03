<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class MoveNStepsStatement extends Statement
{
  const BEGIN_STRING = "move (";
  const END_STRING = ") steps<br/>";

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

    return "Move " . $formula_string_without_markup . " steps";
  }

  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }
}

?>
