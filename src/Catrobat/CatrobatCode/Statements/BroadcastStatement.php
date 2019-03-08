<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class BroadcastStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BroadcastStatement extends Statement
{
  const BEGIN_STRING = "broadcast ";
  const END_STRING = "<br/>";

  /**
   * BroadcastStatement constructor.
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
    return "Broadcast " . $this->xmlTree->broadcastMessage;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
