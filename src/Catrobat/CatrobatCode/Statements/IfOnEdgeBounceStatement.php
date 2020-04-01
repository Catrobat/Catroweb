<?php

namespace App\Catrobat\CatrobatCode\Statements;

class IfOnEdgeBounceStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'if on edge, bounce';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * IfOnEdgeBounceStatement constructor.
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
    return 'If on edge, bounce';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
