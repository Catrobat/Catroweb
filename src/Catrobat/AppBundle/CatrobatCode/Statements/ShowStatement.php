<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class ShowStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class ShowStatement extends Statement
{
  const BEGIN_STRING = "show";
  const END_STRING = "<br/>";

  /**
   * ShowStatement constructor.
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
    return "Show";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }

}
