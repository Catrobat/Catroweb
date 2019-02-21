<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;


/**
 * Class AddItemToUserListStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class AddItemToUserListStatement extends BaseUserListStatement
{

  const BEGIN_STRING = "add item to userlist ";

  const MIDDLE_STRING = "(";

  const END_STRING = ")<br/>";


  /**
   * AddItemToUserListStatement constructor.
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

    $formula_string = $this->getLastChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace("#<[^>]*>#", '', $formula_string);

    return "Add " . $formula_string_without_markup . " to list " . $list_variable_name;
  }


  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_red.png";
  }
}
