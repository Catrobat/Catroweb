<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class FileNameStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, private mixed $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces, $value, '');
  }

  public function execute(): string
  {
    return SyntaxHighlightingConstants::VALUE.$this->value.$this->executeChildren().SyntaxHighlightingConstants::END;
  }

  public function getValue(): mixed
  {
    return $this->value;
  }
}
