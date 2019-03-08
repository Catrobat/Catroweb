<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class VibrationStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class VibrationStatement extends Statement
{
  const BEGIN_STRING = "vibrating for ";
  const END_STRING = " seconds<br/>";

  /**
   * VibrationStatement constructor.
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

    return "Vibrate for " . $formula_string_without_markup . " second(s)";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }

}
