<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class LoopEndStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class LoopEndStatement extends Statement
{
  const BEGIN_STRING = "end of loop";
  const END_STRING = "<br/>";

  /**
   * LoopEndStatement constructor.
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
