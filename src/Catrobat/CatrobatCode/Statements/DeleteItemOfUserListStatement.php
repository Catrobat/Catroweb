<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class DeleteItemOfUserListStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class DeleteItemOfUserListStatement extends BaseUserListStatement
{
  const BEGIN_STRING = "delete item in userlist ";
  const MIDDLE_STRING = "(";
  const END_STRING = ")<br/>";

  /**
   * DeleteItemOfUserListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::MIDDLE_STRING,
      self::END_STRING);
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    $list_variable_name = $this->xmlTree->userList->name;

    $formula_string = $this->getFormulaListChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "Delete item from list " . $list_variable_name . " at position " . $formula_string_without_markup;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_red.png";
  }

}
