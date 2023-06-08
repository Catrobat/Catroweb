<?php

namespace App\Project\CatrobatCode\Statements;

class LeftChildStatement extends FormulaStatement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      '');
  }
}
