<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class ChangeSizeByNStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class ChangeSizeByNStatement extends BaseChangeByNStatement
{
  const BEGIN_STRING = "size";
  const END_STRING = ")%<br/>";

  /**
   * ChangeSizeByNStatement constructor.
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

    return "Change size by " . $formula_string_without_markup;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }
}
