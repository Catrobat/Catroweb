<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicElseStatement extends Statement
{
  final public const string BEGIN_STRING = 'else';
  final public const string END_STRING = '<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
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
