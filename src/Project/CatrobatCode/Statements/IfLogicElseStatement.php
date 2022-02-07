<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicElseStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'else';
  /**
   * @var string
   */
  public const END_STRING = '<br/>';

  /**
   * IfLogicElseStatement constructor.
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

  public function getSpacesForNextBrick(): int
  {
    return $this->spaces + 1;
  }

  public function getBrickText(): string
  {
    return 'Else';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
