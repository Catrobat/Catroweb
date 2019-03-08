<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class ValueStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class ValueStatement extends Statement
{
  /**
   * @var
   */
  private $value;
  /**
   * @var
   */
  private $type;

  /**
   * ValueStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $value
   * @param $type
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value, $type)
  {
    $this->value = $value;
    $this->type = $type;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

  /**
   * @return string
   */
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
