<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\StatementFactory;

class ObjectStatement extends Statement
{
  private string $name;

  public function __construct(StatementFactory $statementFactory, int $spaces, string $name)
  {
    $this->name = $name;
    parent::__construct($statementFactory, null, $spaces, '', '');
  }

  public function execute(): string
  {
    return $this->name;
  }
}
