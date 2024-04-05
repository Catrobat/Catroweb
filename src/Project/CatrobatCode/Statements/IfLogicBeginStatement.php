<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class IfLogicBeginStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'if ';
  /**
   * @var string
   */
  final public const END_STRING = ')<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
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
