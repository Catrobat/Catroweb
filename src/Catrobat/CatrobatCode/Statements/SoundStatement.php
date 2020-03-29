<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\StatementFactory;
use SimpleXMLElement;

class SoundStatement extends Statement
{
  private string $value;

  private ?FileNameStatement $fileName = null;

  private ?ValueStatement $name = null;

  public function __construct(StatementFactory $statementFactory, SimpleXMLElement $xmlTree, int $spaces, string $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  public function execute(): string
  {
    $code = $this->value;
    $this->findNames();

    if (null !== $this->name)
    {
      $code .= $this->name->execute();
    }

    if (null !== $this->fileName)
    {
      $code .= ' (filename: '.$this->fileName->execute().')';
    }

    return $code;
  }

  public function getName(): ?ValueStatement
  {
    return $this->name;
  }

  private function findNames(): void
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if (null != $statement)
      {
        if ($statement instanceof ValueStatement)
        {
          $this->name = $statement;
        }
        elseif ($statement instanceof FileNameStatement)
        {
          $this->fileName = $statement;
        }
      }
    }
  }
}
