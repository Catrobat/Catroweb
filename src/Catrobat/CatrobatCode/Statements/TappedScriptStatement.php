<?php

namespace App\Catrobat\CatrobatCode\Statements;

class TappedScriptStatement extends Statement
{
  private string $BEGIN_STRING = 'when tapped<br/>';

  /**
   * TappedScriptStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $this->BEGIN_STRING,
      '');
  }
}
