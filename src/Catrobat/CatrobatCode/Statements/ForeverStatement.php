<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class ForeverStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'forever';
  /**
   * @var string
   */
  const END_STRING = '<br/>';

  /**
   * ForeverStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP.self::BEGIN_STRING.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $stmt,
      self::END_STRING);
  }

  public function getSpacesForNextBrick(): int
  {
    return $this->spaces + 1;
  }

  public function getBrickText(): string
  {
    return 'Forever';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
