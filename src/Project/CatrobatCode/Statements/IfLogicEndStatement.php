<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicEndStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'end if';

  /**
   * @var string
   */
  final public const END_STRING = '<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP.self::BEGIN_STRING.SyntaxHighlightingConstants::END;

    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      $stmt,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    return 'End If';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
