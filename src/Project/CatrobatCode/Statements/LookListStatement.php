<?php

namespace App\Project\CatrobatCode\Statements;

class LookListStatement extends BaseListStatement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'used looks: <br/>';

  /**
   * LookListStatement constructor.
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
