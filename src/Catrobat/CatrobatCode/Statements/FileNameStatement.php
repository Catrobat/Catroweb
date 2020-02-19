<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class FileNameStatement.
 */
class FileNameStatement extends Statement
{
  /**
   * @var
   */
  private $value;

  /**
   * FileNameStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  /**
   * @return string
   */
  public function execute()
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
