<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class SetLookStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class SetLookStatement extends Statement
{
  const BEGIN_STRING = "switch to look ";
  const END_STRING = "<br/>";

  /**
   * SetLookStatement constructor.
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
    return "Switch to look";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }

}
