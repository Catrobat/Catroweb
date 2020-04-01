<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicBeginStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'if ';
  /**
   * @var string
   */
  const END_STRING = ')<br/>';

  /**
   * IfLogicBeginStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP.self::BEGIN_STRING.SyntaxHighlightingConstants::END.'(';

    parent::__construct($statementFactory, $xmlTree, $spaces,
      $stmt,
      self::END_STRING);
  }

  public function getSpacesForNextBrick(): int
  {
    return $this->spaces + 1;
  }

  public function getBrickText(): string
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace('#<[^>]*>#', '', $formula_string);

    return 'If '.$formula_string_without_markup.' is true then';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_orange.png';
  }
}
