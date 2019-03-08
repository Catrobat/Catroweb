<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class HideStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class HideStatement extends Statement
{
  const BEGIN_STRING = "hide";
  const END_STRING = "<br/>";

  /**
   * HideStatement constructor.
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
    return "Hide";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }
}
