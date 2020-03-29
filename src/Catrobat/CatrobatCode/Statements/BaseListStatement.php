<?php

namespace App\Catrobat\CatrobatCode\Statements;

class BaseListStatement extends Statement
{
  /**
   * BaseListStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $start
   * @param mixed $end
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $start, $end)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  public function execute(): string
  {
    if (count(parent::getStatements()) < 1)
    {
      return '';
    }

    return $this->addSpaces().parent::getBeginString().$this->executeChildren().parent::getEndString();
  }

  public function executeChildren(): string
  {
    $code = '';
    $spacesString = parent::addSpaces(1);
    foreach ($this->statements as $value)
    {
      $code .= $spacesString.$value->execute().'<br/>';
    }

    return $code;
  }
}
