<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class TappedScriptStatement extends Statement
{
  private $BEGIN_STRING = "when tapped<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $this->BEGIN_STRING,
      "");
  }
}

?>
