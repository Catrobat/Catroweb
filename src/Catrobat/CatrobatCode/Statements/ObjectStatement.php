<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class ObjectStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class ObjectStatement extends Statement
{
  /**
   * @var
   */
  private $name;

  /**
   * ObjectStatement constructor.
   *
   * @param $statementFactory
   * @param $spaces
   * @param $name
   */
  public function __construct($statementFactory, $spaces, $name)
  {
    $this->name = $name;
    parent::__construct($statementFactory, null, 0,
      "", "");

  }

  /**
   * @return string
   */
  public function execute()
  {
    return $this->name;
  }
}
