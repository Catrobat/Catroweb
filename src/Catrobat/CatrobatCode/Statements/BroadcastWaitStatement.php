<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class BroadcastWaitStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BroadcastWaitStatement extends Statement
{
  const BEGIN_STRING = "broadcast and wait ";
  const END_STRING = "<br/>";

  /**
   * BroadcastWaitStatement constructor.
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
    return "Broadcast and wait " . $this->xmlTree->broadcastMessage;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
