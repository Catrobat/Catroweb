<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class ScriptListStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      '', '');
  }

  protected function addSpaces(int $offset = 0): string
  {
    return '';
  }
}
