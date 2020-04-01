<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class FileNameStatement extends Statement
{
  private $value;

  /**
   * FileNameStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
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
