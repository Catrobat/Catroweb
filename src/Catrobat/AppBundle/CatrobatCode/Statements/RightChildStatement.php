<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class RightChildStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
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
