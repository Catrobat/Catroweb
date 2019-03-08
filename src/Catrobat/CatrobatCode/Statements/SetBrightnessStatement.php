<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class SetBrightnessStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class SetBrightnessStatement extends BaseSetToStatement
{
  const BEGIN_STRING = "brightness";
  const END_STRING = ")%<br/>";

  /**
   * SetBrightnessStatement constructor.
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

    return "Set brightness to " . $formula_string_without_markup . "%";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }

}
