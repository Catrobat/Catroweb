<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class TurnRightStatement extends Statement
{
  final public const string BEGIN_STRING = 'turn right (';

  final public const string END_STRING = ') degrees<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace('#<[^>]*>#', '', $formula_string);

    return 'Turn right '.$formula_string_without_markup.' degrees';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
