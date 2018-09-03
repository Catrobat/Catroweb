<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class BaseListStatement extends Statement
{

  public function __construct($statementFactory, $xmlTree, $spaces, $start, $end)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  public function execute()
  {
    if (count(parent::getStatements()) < 1)
    {
      return '';
    }
    $code = $this->addSpaces() . parent::getBeginString() . $this->executeChildren() . parent::getEndString();

    return $code;
  }

  public function executeChildren()
  {
    $code = '';
    $spacesString = parent::addSpaces(1);
    foreach ($this->statements as $value)
    {
      $code .= $spacesString . $value->execute() . "<br/>";
    }

    return $code;
  }
}

?>
