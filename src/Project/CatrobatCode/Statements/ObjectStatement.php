<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\StatementFactory;

class ObjectStatement extends Statement
{
  public function __construct(StatementFactory $statementFactory, int $spaces, private readonly string $name)
  {
    parent::__construct($statementFactory, null, $spaces, '', '');
  }

  public function execute(): string
  {
    return $this->name;
  }
}
