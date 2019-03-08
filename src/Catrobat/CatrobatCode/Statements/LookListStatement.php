<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class LookListStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class LookListStatement extends BaseListStatement
{
  const BEGIN_STRING = "used looks: <br/>";

  /**
   * LookListStatement constructor.
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
