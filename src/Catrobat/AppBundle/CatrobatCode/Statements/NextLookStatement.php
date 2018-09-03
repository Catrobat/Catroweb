<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class NextLookStatement extends Statement
{
  const BEGIN_STRING = "next look";
  const END_STRING = "<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText()
  {
    return "Next look";
  }

  public function getBrickColor()
  {
    return "1h_brick_green.png";
  }

}

?>
