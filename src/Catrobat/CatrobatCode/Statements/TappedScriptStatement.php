<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class TappedScriptStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class TappedScriptStatement extends Statement
{
  private $BEGIN_STRING = "when tapped<br/>";

  /**
   * TappedScriptStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $this->BEGIN_STRING,
      "");
  }
}
