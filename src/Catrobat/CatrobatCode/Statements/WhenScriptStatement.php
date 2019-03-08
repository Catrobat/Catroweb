<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class WhenScriptStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class WhenScriptStatement extends Statement
{
  const BEGIN_STRING = "when program started <br/>";

  /**
   * WhenScriptStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      "");
  }
}
