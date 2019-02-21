<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;


/**
 * Class BaseSetToStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class BaseSetToStatement extends Statement
{

  /**
   * BaseSetToStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $beginString
   * @param $endString
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $beginString = "set " . SyntaxHighlightingConstants::VARIABLES . $beginString . SyntaxHighlightingConstants::END . " to (";
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $beginString,
      $endString);
  }
}
