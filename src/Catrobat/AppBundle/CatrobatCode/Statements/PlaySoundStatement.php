<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class PlaySoundStatement extends Statement
{
  const BEGIN_STRING = "start sound ";
  const END_STRING = "<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText()
  {
    return "Start sound";
  }

  public function getBrickColor()
  {
    return "1h_brick_violet.png";
  }

}

?>
