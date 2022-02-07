<?php

namespace App\Project\CatrobatCode\Statements;

class GoNStepsBackStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'go back (';
  /**
   * @var string
   */
  public const END_STRING = ') layers<br/>';

  /**
   * GoNStepsBackStatement constructor.
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

    return 'Go back '.$formula_string_without_markup.' layer(s)';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
