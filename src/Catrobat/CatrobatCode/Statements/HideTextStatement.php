<?php

namespace App\Catrobat\CatrobatCode\Statements;

class HideTextStatement extends Statement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'hide variable ';
  /**
   * @var string
   */
  const END_STRING = ')<br/>';

  /**
   * HideTextStatement constructor.
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
    $variable_name = $this->xmlTree->userVariableName;

    return 'Hide variable '.$variable_name;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_red.png';
  }
}
