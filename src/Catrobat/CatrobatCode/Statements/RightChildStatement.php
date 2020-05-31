<?php

namespace App\Catrobat\CatrobatCode\Statements;

class RightChildStatement extends FormulaStatement
{
  /**
   * RightChildStatement constructor.
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
