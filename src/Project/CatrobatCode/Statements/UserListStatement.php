<?php

namespace App\Project\CatrobatCode\Statements;

class UserListStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, mixed $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces, $value, '');
  }
}
