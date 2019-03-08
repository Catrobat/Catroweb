<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class NextLookStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class NextLookStatement extends Statement
{
  const BEGIN_STRING = "next look";
  const END_STRING = "<br/>";

  /**
   * NextLookStatement constructor.
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
    return "Next look";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }

}
