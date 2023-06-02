<?php

namespace App\Project\CatrobatCode\Statements;

class BaseUserListStatement extends Statement
{
  private string $listName;

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, private mixed $start, private mixed $middle, private mixed $end)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  public function execute(): string
  {
    $children = $this->executeChildren();

    return parent::addSpaces().$this->start.$this->listName.$this->middle.$children.$this->end;
  }

  public function executeChildren(): string
  {
    $code = '';

    foreach ($this->statements as $value) {
      if ($value instanceof UserListStatement) {
        $this->listName = $value->execute();
      } else {
        $code .= $value->execute();
      }
    }

    return $code;
  }
}
