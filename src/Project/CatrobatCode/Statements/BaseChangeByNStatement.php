<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class BaseChangeByNStatement extends Statement
{
  /**
   * BaseChangeByNStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $beginString
   * @param mixed $endString
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $beginString = 'change '.SyntaxHighlightingConstants::VARIABLES.$beginString.SyntaxHighlightingConstants::END.' by (';
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $beginString,
      $endString);
  }
}
