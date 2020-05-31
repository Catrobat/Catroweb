<?php

namespace App\Catrobat\CatrobatCode\Statements;

class MoveNStepsStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'move (';
  /**
   * @var string
   */
  const END_STRING = ') steps<br/>';

  /**
   * MoveNStepsStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace('#<[^>]*>#', '', $formula_string);

    return 'Move '.$formula_string_without_markup.' steps';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
