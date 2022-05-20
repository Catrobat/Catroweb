<?php

namespace App\Project\CatrobatCode\Statements;

class ShowStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'show';
  /**
   * @var string
   */
  final public const END_STRING = '<br/>';

  /**
   * ShowStatement constructor.
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
    return 'Show';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_green.png';
  }
}
