<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\StatementFactory;
use App\Project\CatrobatCode\SyntaxHighlightingConstants;

class LookStatement extends Statement
{
  private ?Statement $fileName = null;

  public function __construct(StatementFactory $statementFactory, \SimpleXMLElement $xmlTree, int $spaces, private readonly ?string $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  #[\Override]
  public function execute(): string
  {
    $this->findNames();

    $code = '';

    if (null !== $this->value) {
      $code = SyntaxHighlightingConstants::VARIABLES.$this->value.SyntaxHighlightingConstants::END;
    }

    if ($this->fileName instanceof Statement) {
      $code .= ' (filename: '.$this->fileName->execute().')';
    }

    return $code;
  }

  public function getValue(): ?string
  {
    return $this->value;
  }

  public function getFileName(): ?Statement
  {
    return $this->fileName;
  }

  private function findNames(): void
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement) {
      if (null == $statement) {
        continue;
      }
      if (!$statement instanceof FileNameStatement) {
        continue;
      }
      $this->fileName = $statement;
    }
  }
}
