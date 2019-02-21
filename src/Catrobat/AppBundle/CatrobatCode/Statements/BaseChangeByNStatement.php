<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class BaseChangeByNStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
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
