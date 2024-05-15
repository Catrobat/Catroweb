<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class BroadcastWaitStatement extends Statement
{
  final public const string BEGIN_STRING = 'broadcast and wait ';
  final public const string END_STRING = '<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
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
