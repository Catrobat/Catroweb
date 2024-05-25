<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class ReplaceItemInUserListStatement extends BaseUserListStatement
{
  final public const string BEGIN_STRING = 'replace item in userlist ';

  final public const string MIDDLE_STRING = '(';

  final public const string END_STRING = ')<br/>';

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
    $formula_string_index = null;
    $formula_string_value = null;

    foreach ($this->getFormulaListChildStatement()->getStatements() as $statement) {
      if ($statement instanceof FormulaStatement) {
        if ('REPLACE_ITEM_IN_USERLIST_INDEX' === $statement->getCategory()) {
          $formula_string_index = $statement->execute();
        } else {
          $formula_string_value = $statement->execute();
        }
      }
    }

    $formula_str_index_no_markup = preg_replace('#<[^>]*>#', '', (string) $formula_string_index);
    $formula_str_value_no_markup = preg_replace('#<[^>]*>#', '', (string) $formula_string_value);

    return 'Replace item in list '.$list_variable_name.' at position '.$formula_str_index_no_markup.' with '.$formula_str_value_no_markup;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_red.png';
  }
}
