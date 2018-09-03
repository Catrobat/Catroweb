<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class FileNameStatement extends Statement
{
  private $value;

  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

  public function execute()
  {
    $code = SyntaxHighlightingConstants::VALUE . $this->value . $this->executeChildren() . SyntaxHighlightingConstants::END;

    return $code;
  }

  public function getValue()
  {
    return $this->value;
  }
}

?>
