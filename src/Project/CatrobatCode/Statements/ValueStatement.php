<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class ValueStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, private readonly mixed $value, private readonly mixed $type)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces, $value, '');
  }

  #[\Override]
  public function execute(): string
  {
    $color = match ((string) $this->type) {
      'FUNCTION', 'OPERATOR' => SyntaxHighlightingConstants::FUNCTIONS,
      'STRING', 'NUMBER' => SyntaxHighlightingConstants::VALUE,
      default => SyntaxHighlightingConstants::VARIABLES,
    };

    return $color.$this->value.SyntaxHighlightingConstants::END.$this->executeChildren();
  }
}
