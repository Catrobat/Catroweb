<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class StopAllSoundsStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class StopAllSoundsStatement extends Statement
{
  const BEGIN_STRING = "stop all sounds";
  const END_STRING = "<br/>";

  /**
   * StopAllSoundsStatement constructor.
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
    return "Sop all sounds";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_violet.png";
  }

}
