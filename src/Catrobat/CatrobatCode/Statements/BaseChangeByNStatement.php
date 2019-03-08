<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class BaseChangeByNStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BaseChangeByNStatement extends Statement
{

  /**
   * BaseChangeByNStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $beginString
   * @param $endString
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $beginString = "change " . SyntaxHighlightingConstants::VARIABLES . $beginString . SyntaxHighlightingConstants::END . " by (";
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $beginString,
      $endString);
  }
}
