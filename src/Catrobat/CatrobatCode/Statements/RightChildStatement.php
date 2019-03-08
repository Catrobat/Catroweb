<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class RightChildStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class RightChildStatement extends FormulaStatement
{

  /**
   * RightChildStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      "");
  }
}
