<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class RightChildStatement extends FormulaStatement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      '');
  }
}
