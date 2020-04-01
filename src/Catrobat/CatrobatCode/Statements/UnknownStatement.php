<?php

namespace App\Catrobat\CatrobatCode\Statements;

class UnknownStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'Unknown Statement';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * UnknownStatement constructor.
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
    return 'Unknown Brick';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_grey.png';
  }
}
