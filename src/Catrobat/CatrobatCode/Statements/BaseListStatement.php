<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class BaseListStatement.
 */
class BaseListStatement extends Statement
{
  /**
   * BaseListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $start
   * @param $end
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $start, $end)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  /**
   * @return string
   */
  public function execute()
  {
    if (count(parent::getStatements()) < 1)
    {
      return '';
    }

    return $this->addSpaces().parent::getBeginString().$this->executeChildren().parent::getEndString();
  }

  /**
   * @return string
   */
  public function executeChildren()
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
