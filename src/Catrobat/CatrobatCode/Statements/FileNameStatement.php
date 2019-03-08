<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class FileNameStatement
 * @package App\Catrobat\CatrobatCode\Statements
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
      "");
  }

  /**
   * @return string
   */
  public function execute()
  {
    $code = SyntaxHighlightingConstants::VALUE . $this->value . $this->executeChildren() . SyntaxHighlightingConstants::END;

    return $code;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }
}
