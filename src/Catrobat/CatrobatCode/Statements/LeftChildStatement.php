<?php

namespace App\Catrobat\CatrobatCode\Statements;

class LeftChildStatement extends FormulaStatement
{
  /**
   * LeftChildStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      '');
  }
}
