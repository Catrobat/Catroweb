<?php

namespace App\Catrobat\CatrobatCode\Statements;

class TurnLeftStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'turn left (';
  /**
   * @var string
   */
  const END_STRING = ') degrees<br/>';

  /**
   * TurnLeftStatement constructor.
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

    return 'Turn left '.$formula_string_without_markup.' degrees';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
