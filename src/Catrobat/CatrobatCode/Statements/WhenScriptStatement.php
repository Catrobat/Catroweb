<?php

namespace App\Catrobat\CatrobatCode\Statements;

class WhenScriptStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'when program started <br/>';

  /**
   * WhenScriptStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }
}
