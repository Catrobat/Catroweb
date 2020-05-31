<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class UserVariableStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = '';

  /**
   * @var string
   */
  const AT_END_STRING = ' at (';
  /**
   * @var string
   */
  const TO_END_STRING = ' to (';

  /**
   * UserVariableStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value, bool $useAt = false)
  {
    $end = self::TO_END_STRING;
    if ($useAt)
    {
      $end = self::AT_END_STRING;
    }

    $value = SyntaxHighlightingConstants::VARIABLES.$value.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      $end);
  }
}
