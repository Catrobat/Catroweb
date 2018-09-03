<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class ReceivedMessageStatement extends Statement
{

  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $value = SyntaxHighlightingConstants::VALUE . $value . SyntaxHighlightingConstants::END;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

}

?>
