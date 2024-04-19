<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class BaseChangeByNStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, mixed $beginString, mixed $endString)
  {
    $beginString = 'change '.SyntaxHighlightingConstants::VARIABLES.$beginString.SyntaxHighlightingConstants::END.' by (';
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $beginString,
      $endString);
  }
}
