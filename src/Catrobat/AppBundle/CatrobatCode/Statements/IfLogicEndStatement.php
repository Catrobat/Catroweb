<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class IfLogicEndStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class IfLogicEndStatement extends Statement
{
  /**
   *
   */
  const BEGIN_STRING = "end if";
  /**
   *
   */
  const END_STRING = "<br/>";

  /**
   * IfLogicEndStatement constructor.
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
   * @return string
   */
  public function getBrickText()
  {
    return "End If";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
