<?php

namespace App\Catrobat\CatrobatCode\Statements;

class AddItemToUserListStatement extends BaseUserListStatement
{
  /**
   * @var string
   */
  const BEGIN_STRING = 'add item to userlist ';

  /**
   * @var string
   */
  const MIDDLE_STRING = '(';

  /**
   * @var string
   */
  const END_STRING = ')<br/>';

  /**
   * AddItemToUserListStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::MIDDLE_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    $list_variable_name = $this->xmlTree->userList->name;

    $formula_string = $this->getLastChildStatement()->executeChildren();
    $formula_string_without_markup = preg_replace('#<[^>]*>#', '', $formula_string);

    return 'Add '.$formula_string_without_markup.' to list '.$list_variable_name;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_red.png';
  }
}
