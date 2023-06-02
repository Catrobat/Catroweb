<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class BroadcastMessageStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, mixed $value)
  {
    $value = SyntaxHighlightingConstants::VALUE.$value.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value, '');
  }
}
