<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class RepeatStatement extends Statement
{
  const BEGIN_STRING = "repeat ";
  const END_STRING = ")<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END . "(";
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $stmt,
      self::END_STRING);
  }

  public function getSpacesForNextBrick()
  {
    return $this->spaces + 1;
  }

  public function getBrickText()
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "Repeat " . $formula_string_without_markup . " times";
  }

  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }

}

?>
