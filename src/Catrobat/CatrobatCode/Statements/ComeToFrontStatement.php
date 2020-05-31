<?php

namespace App\Catrobat\CatrobatCode\Statements;

class ComeToFrontStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'come to front';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * ComeToFrontStatement constructor.
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
    return 'Go to front';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
