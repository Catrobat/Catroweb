<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\StatementFactory;
use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;
use SimpleXMLElement;

class LookStatement extends Statement
{
  private ?string $value = null;

  private ?Statement $fileName = null;

  public function __construct(StatementFactory $statementFactory, SimpleXMLElement $xmlTree, int $spaces, string $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  public function execute(): string
  {
    $this->findNames();

    $code = '';

    if (null !== $this->value)
    {
      $code = SyntaxHighlightingConstants::VARIABLES.$this->value.SyntaxHighlightingConstants::END;
    }
    if (null !== $this->fileName)
    {
      $code .= ' (filename: '.$this->fileName->execute().')';
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @return mixed
   */
  public function getFileName()
  {
    return $this->fileName;
  }

  private function findNames(): void
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if (null != $statement && $statement instanceof FileNameStatement)
      {
        $this->fileName = $statement;
      }
    }
  }
}
