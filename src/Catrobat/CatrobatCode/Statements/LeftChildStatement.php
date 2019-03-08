<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class LeftChildStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class LeftChildStatement extends FormulaStatement
{

  /**
   * LeftChildStatement constructor.
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
