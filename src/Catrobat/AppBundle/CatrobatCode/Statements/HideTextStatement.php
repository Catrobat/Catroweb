<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class HideTextStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class HideTextStatement extends Statement
{
  const BEGIN_STRING = "hide variable ";
  const END_STRING = ")<br/>";


  /**
   * HideTextStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    $variable_name = $this->xmlTree->userVariableName;

    return "Hide variable " . $variable_name;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_red.png";
  }
}
