<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class BaseUserListStatement extends Statement
{
  private $start;
  private $middle;
  private $end;
  private $listName;

  public function __construct($statementFactory, $xmlTree, $spaces, $start, $middle, $end)
  {
    $this->start = $start;
    $this->middle = $middle;
    $this->end = $end;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  public function execute()
  {
    $children = $this->executeChildren();
    $code = parent::addSpaces() . $this->start . $this->listName . $this->middle . $children . $this->end;

    return $code;
  }

  public function executeChildren()
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

?>
