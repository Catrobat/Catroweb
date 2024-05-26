<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class UserVariableStatement extends Statement
{
  final public const string BEGIN_STRING = '';

  final public const string AT_END_STRING = ' at (';

  final public const string TO_END_STRING = ' to (';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, mixed $value, bool $useAt = false)
  {
    $end = self::TO_END_STRING;
    if ($useAt) {
      $end = self::AT_END_STRING;
    }

    $value = SyntaxHighlightingConstants::VARIABLES.$value.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      $end);
  }
}
