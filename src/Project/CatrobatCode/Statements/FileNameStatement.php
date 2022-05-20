<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class FileNameStatement extends Statement
{
  /**
   * FileNameStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, private $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  public function execute(): string
  {
    return SyntaxHighlightingConstants::VALUE.$this->value.$this->executeChildren().SyntaxHighlightingConstants::END;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }
}
