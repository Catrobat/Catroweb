<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class SetYStatement extends BaseSetToStatement
{
  const BEGIN_STRING = "Y";
  const END_STRING = ")<br/>";

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

    return "Set Y to " . $formula_string_without_markup;
  }

  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }

}

?>
