<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class PointToStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class PointToStatement extends Statement
{
  const BEGIN_STRING = "point to ";
  const END_STRING = "<br/>";

  /**
   * PointToStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    return "Point towards " . $this->xmlTree->pointedObject['name'];
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_blue.png";
  }

}
