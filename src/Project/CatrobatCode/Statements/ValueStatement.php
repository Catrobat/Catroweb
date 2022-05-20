<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class ValueStatement extends Statement
{
  /**
   * ValueStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   * @param mixed $type
   */
  public function __construct($statementFactory, $xmlTree, $spaces, private $value, private $type)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces, $value, '');
  }

  public function execute(): string
  {
    $color = match ((string) $this->type) {
      'FUNCTION' => SyntaxHighlightingConstants::FUNCTIONS,
      'OPERATOR' => SyntaxHighlightingConstants::FUNCTIONS,
      'STRING' => SyntaxHighlightingConstants::VALUE,
      'NUMBER' => SyntaxHighlightingConstants::VALUE,
      default => SyntaxHighlightingConstants::VARIABLES,
    };

    return $color.$this->value.SyntaxHighlightingConstants::END.$this->executeChildren();
  }
}
