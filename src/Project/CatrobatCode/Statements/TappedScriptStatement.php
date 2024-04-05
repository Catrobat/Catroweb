<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class TappedScriptStatement extends Statement
{
  private string $BEGIN_STRING = 'when tapped<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $this->BEGIN_STRING,
      '');
  }
}
