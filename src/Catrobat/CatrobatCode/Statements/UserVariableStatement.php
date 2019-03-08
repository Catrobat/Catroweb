<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class UserVariableStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class UserVariableStatement extends Statement
{

  const BEGIN_STRING = "";

  const AT_END_STRING = " at (";
  const TO_END_STRING = " to (";

  /**
   * UserVariableStatement constructor.
   *
   * @param      $statementFactory
   * @param      $xmlTree
   * @param      $spaces
   * @param      $value
   * @param bool $useAt
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value, $useAt = false)
  {
    $end = self::TO_END_STRING;
    if ($useAt)
    {
      $end = self::AT_END_STRING;
    }

    $value = SyntaxHighlightingConstants::VARIABLES . $value . SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      $end);
  }

}
