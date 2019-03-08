<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class PlaySoundStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class PlaySoundStatement extends Statement
{
  const BEGIN_STRING = "start sound ";
  const END_STRING = "<br/>";

  /**
   * PlaySoundStatement constructor.
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
    return "Start sound";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_violet.png";
  }

}
