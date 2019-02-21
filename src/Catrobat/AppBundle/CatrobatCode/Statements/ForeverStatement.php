<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class ForeverStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class ForeverStatement extends Statement
{
  const BEGIN_STRING = "forever";
  const END_STRING = "<br/>";

  /**
   * ForeverStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
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
    return "Forever";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
