<?php

namespace App\Project\CatrobatCode\Statements;

class HideStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'hide';
  /**
   * @var string
   */
  public const END_STRING = '<br/>';

  /**
   * HideStatement constructor.
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
    return 'Hide';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_green.png';
  }
}
