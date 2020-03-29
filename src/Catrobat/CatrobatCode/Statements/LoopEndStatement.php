<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class LoopEndStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'end of loop';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * LoopEndStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP.self::BEGIN_STRING.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      $stmt,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    return 'End of loop';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
