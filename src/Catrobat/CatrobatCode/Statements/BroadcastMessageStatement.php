<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class BroadcastMessageStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BroadcastMessageStatement extends Statement
{

  /**
   * BroadcastMessageStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $value = SyntaxHighlightingConstants::VALUE . $value . SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value, "");
  }

}
