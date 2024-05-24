<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\StatementFactory;

class SoundStatement extends Statement
{
  private ?FileNameStatement $fileName = null;

  private ?ValueStatement $name = null;

  public function __construct(StatementFactory $statementFactory, \SimpleXMLElement $xmlTree, int $spaces, private readonly string $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  #[\Override]
  public function execute(): string
  {
    $code = $this->value;
    $this->findNames();

    if ($this->name instanceof ValueStatement) {
      $code .= $this->name->execute();
    }

    if ($this->fileName instanceof FileNameStatement) {
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
    foreach ($tmpStatements as $statement) {
      if (null != $statement) {
        if ($statement instanceof ValueStatement) {
          $this->name = $statement;
        } elseif ($statement instanceof FileNameStatement) {
          $this->fileName = $statement;
        }
      }
    }
  }
}
