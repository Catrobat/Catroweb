<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class ValueStatement extends Statement
{
  private $value;

  private $type;

  /**
   * ValueStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   * @param mixed $type
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value, $type)
  {
    $this->value = $value;
    $this->type = $type;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  public function execute(): string
  {
    $color = SyntaxHighlightingConstants::VARIABLES;
    switch ($this->type)
    {
      case 'FUNCTION':
        $color = SyntaxHighlightingConstants::FUNCTIONS;
        break;
      case 'OPERATOR':
        $color = SyntaxHighlightingConstants::FUNCTIONS;
        break;
      case 'STRING':
        $color = SyntaxHighlightingConstants::VALUE;
        break;
      case 'NUMBER':
        $color = SyntaxHighlightingConstants::VALUE;
        break;
    }

    return $color.$this->value.SyntaxHighlightingConstants::END.$this->executeChildren();
  }
}
