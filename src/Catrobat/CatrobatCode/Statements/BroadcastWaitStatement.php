<?php

namespace App\Catrobat\CatrobatCode\Statements;

class BroadcastWaitStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'broadcast and wait ';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * BroadcastWaitStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    return 'Broadcast and wait '.$this->xmlTree->broadcastMessage;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
