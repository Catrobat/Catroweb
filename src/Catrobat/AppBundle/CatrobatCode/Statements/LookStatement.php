<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\SyntaxHighlightingConstants;

class LookStatement extends Statement
{

  private $value;
  private $fileName;

  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

  public function execute()
  {
    $this->findNames();

    $code = '';

    if ($this->value != null)
    {
      $code = SyntaxHighlightingConstants::VARIABLES . $this->value . SyntaxHighlightingConstants::END;
    }
    if ($this->fileName != null)
    {
      $code .= ' (filename: ' . $this->fileName->execute() . ')';
    }

    return $code;
  }


  private function findNames()
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if ($statement != null)
      {
        if ($statement instanceof FileNameStatement)
        {
          $this->fileName = $statement;
        }
      }
    }
  }

  public function getValue()
  {
    return $this->value;
  }

  public function getFileName()
  {
    return $this->fileName;
  }
}

?>
