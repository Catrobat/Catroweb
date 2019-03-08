<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class IfLogicBeginStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class IfLogicBeginStatement extends Statement
{
  const BEGIN_STRING = "if ";
  const END_STRING = ")<br/>";

  /**
   * IfLogicBeginStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END . "(";

    parent::__construct($statementFactory, $xmlTree, $spaces,
      $stmt,
      self::END_STRING);
  }

  /**
   * @return int
   */
  public function getSpacesForNextBrick()
  {
    return $this->spaces + 1;
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "If " . $formula_string_without_markup . " is true then";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
