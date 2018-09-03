<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class ForeverStatement extends Statement
{
  const BEGIN_STRING = "forever";
  const END_STRING = "<br/>";

  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    $stmt = SyntaxHighlightingConstants::LOOP . self::BEGIN_STRING . SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $stmt,
      self::END_STRING);
  }

  public function getSpacesForNextBrick()
  {
    return $this->spaces + 1;
  }

  public function getBrickText()
  {
    return "Forever";
  }

  public function getBrickColor()
  {
    return "1h_brick_orange.png";
  }
}

?>
