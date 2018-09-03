<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class LookListStatement extends BaseListStatement
{
  const BEGIN_STRING = "used looks: <br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      "");
  }
}

?>
