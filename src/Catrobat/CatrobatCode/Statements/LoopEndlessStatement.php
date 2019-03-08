<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class LoopEndlessStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class LoopEndlessStatement extends Statement
{
  const BEGIN_STRING = "endless loop";
  const END_STRING = "<br/>";

  /**
   * LoopEndlessStatement constructor.
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
    return "End of loop";
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}
