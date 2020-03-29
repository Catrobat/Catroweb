<?php

namespace App\Catrobat\CatrobatCode\Statements;

class UserListStatement extends Statement
{
  /**
   * UserListStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }
}
