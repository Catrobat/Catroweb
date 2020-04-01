<?php

namespace App\Catrobat\CatrobatCode\Statements;

class BaseUserListStatement extends Statement
{
  private $start;

  private $middle;

  private $end;

  private string $listName;

  /**
   * BaseUserListStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $start
   * @param mixed $middle
   * @param mixed $end
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $start, $middle, $end)
  {
    $this->start = $start;
    $this->middle = $middle;
    $this->end = $end;
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

    foreach ($this->statements as $value)
    {
      if ($value instanceof UserListStatement)
      {
        $this->listName = $value->execute();
      }
      else
      {
        $code .= $value->execute();
      }
    }

    return $code;
  }
}
