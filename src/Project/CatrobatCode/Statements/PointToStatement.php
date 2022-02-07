<?php

namespace App\Project\CatrobatCode\Statements;

class PointToStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'point to ';
  /**
   * @var string
   */
  public const END_STRING = '<br/>';

  /**
   * PointToStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    return 'Point towards '.$this->xmlTree->pointedObject['name'];
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
