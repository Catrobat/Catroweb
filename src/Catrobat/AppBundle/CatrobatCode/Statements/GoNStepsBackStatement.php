<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class GoNStepsBackStatement extends Statement
{
  const BEGIN_STRING = "go back (";
  const END_STRING = ") layers<br/>";

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

    return "Go back " . $formula_string_without_markup . " layer(s)";
  }

  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }
}

?>
