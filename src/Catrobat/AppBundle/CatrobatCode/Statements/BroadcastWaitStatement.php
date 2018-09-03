<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class BroadcastWaitStatement extends Statement
{
  const BEGIN_STRING = "broadcast and wait ";
  const END_STRING = "<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText()
  {
    return "Broadcast and wait " . $this->xmlTree->broadcastMessage;
  }

  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}

?>
