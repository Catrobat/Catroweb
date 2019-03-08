<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class UnknownStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class UnknownStatement extends Statement
{
  const BEGIN_STRING = "Unknown Statement";
  const END_STRING = "<br/>";

  /**
   * UnknownStatement constructor.
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
    return "Unknown Brick";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_grey.png";
  }

}
