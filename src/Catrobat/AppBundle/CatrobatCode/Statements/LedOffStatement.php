<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class LedOffStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class LedOffStatement extends Statement
{
  const BEGIN_STRING = "led off";
  const END_STRING = "<br/>";

  /**
   * LedOffStatement constructor.
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
    return "Turn flashlight off";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }
}
