<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class ScriptListStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class ScriptListStatement extends Statement
{

  /**
   * ScriptListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      "", "");
  }

  /**
   * @param int $offset
   *
   * @return string
   */
  protected function addSpaces($offset = 0)
  {
    return "";
  }
}
