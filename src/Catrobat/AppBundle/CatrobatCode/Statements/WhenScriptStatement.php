<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class WhenScriptStatement extends Statement
{
  const BEGIN_STRING = "when program started <br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      "");
  }
}

?>
