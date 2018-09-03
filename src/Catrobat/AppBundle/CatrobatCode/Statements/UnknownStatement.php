<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class UnknownStatement extends Statement
{
  const BEGIN_STRING = "Unknown Statement";
  const END_STRING = "<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText()
  {
    return "Unknown Brick";
  }

  public function getBrickColor()
  {
    return "1h_brick_grey.png";
  }

}

?>
