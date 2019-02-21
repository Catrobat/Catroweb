<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class TurnRightStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class TurnRightStatement extends Statement
{
  const BEGIN_STRING = "turn right (";
  const END_STRING = ") degrees<br/>";

  /**
   * TurnRightStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "Turn right " . $formula_string_without_markup . " degrees";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }

}
