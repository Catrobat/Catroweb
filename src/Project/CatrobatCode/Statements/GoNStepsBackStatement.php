<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class GoNStepsBackStatement extends Statement
{
  final public const string BEGIN_STRING = 'go back (';

  final public const string END_STRING = ') layers<br/>';

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

    return 'Go back '.$formula_string_without_markup.' layer(s)';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
