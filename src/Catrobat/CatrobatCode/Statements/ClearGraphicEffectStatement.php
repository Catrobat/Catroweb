<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class ClearGraphicEffectStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class ClearGraphicEffectStatement extends Statement
{
  const BEGIN_STRING = "clear graphic effects";
  const END_STRING = "<br/>";

  /**
   * ClearGraphicEffectStatement constructor.
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
    return "Clear graphic effects";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }
}
