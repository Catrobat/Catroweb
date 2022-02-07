<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class BaseSetToStatement extends Statement
{
  /**
   * BaseSetToStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $beginString
   * @param mixed $endString
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $beginString = 'set '.SyntaxHighlightingConstants::VARIABLES.$beginString.SyntaxHighlightingConstants::END.' to (';
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $beginString,
      $endString);
  }
}
