<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class IfLogicElseStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class IfLogicElseStatement extends Statement
{
  const BEGIN_STRING = "else";
  const END_STRING = "<br/>";

  /**
   * IfLogicElseStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END;

    parent::__construct($statementFactory, $xmlTree, $spaces - 1,
      $stmt,
      self::END_STRING);
  }

  /**
   * @return int
   */
  public function getSpacesForNextBrick()
  {
    return $this->spaces + 1;
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    return "Else";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
