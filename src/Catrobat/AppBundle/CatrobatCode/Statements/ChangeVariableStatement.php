<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class ChangeVariableStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class ChangeVariableStatement extends Statement
{
  const BEGIN_STRING = "change ";
  const END_STRING = ")<br/>";

  /**
   * ChangeVariableStatement constructor.
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
    $variable_name = $this->xmlTree->userVariable;

    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "Change variable " . $variable_name . " by " . $formula_string_without_markup;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_red.png";
  }
}
