<?php

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class UserVariableStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = '';

  /**
   * @var string
   */
  public const AT_END_STRING = ' at (';
  /**
   * @var string
   */
  public const TO_END_STRING = ' to (';

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
    if ($useAt) {
      $end = self::AT_END_STRING;
    }

    $value = SyntaxHighlightingConstants::VARIABLES.$value.SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      $end);
  }
}
