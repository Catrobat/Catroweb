<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class ValueStatement extends Statement
{
  private $value;
  private $type;

  public function __construct($statementFactory, $xmlTree, $spaces, $value, $type)
  {
    $this->value = $value;
    $this->type = $type;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

  public function execute()
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

    $code = $color . $this->value . SyntaxHighlightingConstants::END . $this->executeChildren();

    return $code;
  }
}

?>
