<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class BaseUserListStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BaseUserListStatement extends Statement
{

  /**
   * @var
   */
  private $start;

  /**
   * @var
   */
  private $middle;

  /**
   * @var
   */
  private $end;

  /**
   * @var
   */
  private $listName;


  /**
   * BaseUserListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $start
   * @param $middle
   * @param $end
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


  /**
   * @return string
   */
  public function execute()
  {
    $children = $this->executeChildren();
    $code = parent::addSpaces() . $this->start . $this->listName . $this->middle . $children . $this->end;

    return $code;
  }


  /**
   * @return string
   */
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
