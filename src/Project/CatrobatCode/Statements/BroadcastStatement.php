<?php

namespace App\Project\CatrobatCode\Statements;

class BroadcastStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'broadcast ';
  /**
   * @var string
   */
  final public const END_STRING = '<br/>';

  /**
   * BroadcastStatement constructor.
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
    return 'Broadcast '.$this->xmlTree->broadcastMessage;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
