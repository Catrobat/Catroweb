<?php

namespace App\Catrobat\CatrobatCode\Statements;

class ScriptListStatement extends Statement
{
  /**
   * ScriptListStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      '', '');
  }

  protected function addSpaces(int $offset = 0): string
  {
    return '';
  }
}
