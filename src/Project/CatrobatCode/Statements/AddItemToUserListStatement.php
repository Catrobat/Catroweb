<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class AddItemToUserListStatement extends BaseUserListStatement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'add item to userlist ';

  /**
   * @var string
   */
  final public const MIDDLE_STRING = '(';

  /**
   * @var string
   */
  final public const END_STRING = ')<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
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
