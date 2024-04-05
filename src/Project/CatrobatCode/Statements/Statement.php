<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\StatementFactory;

class Statement
{
  protected array $statements = [];

  public function __construct(StatementFactory $statementFactory, protected ?\SimpleXMLElement $xmlTree, protected int $spaces, private readonly string $beginString, private readonly string $endString)
  {
    $this->createChildren($statementFactory);
  }

  public function execute(): string
  {
    return $this->addSpaces().$this->beginString.$this->executeChildren().$this->endString;
  }

  public function executeChildren(): string
  {
    $code = '';

    foreach ($this->statements as $value) {
      $code .= $value->execute();
    }

    return $code;
  }

  public function getSpacesForNextBrick(): int
  {
    return $this->spaces;
  }

  public function getStatements(): array
  {
    return $this->statements;
  }

  public function getBeginString(): string
  {
    return $this->beginString;
  }

  public function getEndString(): string
  {
    return $this->endString;
  }

  public function getXmlTree(): ?\SimpleXMLElement
  {
    return $this->xmlTree;
  }

  public function getClassName(): string
  {
    return static::class;
  }

  protected function createChildren(StatementFactory $statementFactory): void
  {
    if (null != $this->xmlTree) {
      $this->addAllScripts($statementFactory->createStatement($this->xmlTree, $this->spaces + 1));
    }
  }

  /**
   * @param Statement[] $statementsToAdd
   */
  protected function addAllScripts(array $statementsToAdd): void
  {
    foreach ($statementsToAdd as $statement) {
      $this->statements[] = $statement;
    }
  }

  protected function addSpaces(int $offset = 0): string
  {
    return str_repeat('&nbsp;', max(($this->spaces + $offset) * 4, 0));
  }

  protected function getLastChildStatement(): Statement
  {
    $child_statement_keys = array_keys($this->statements);
    $last_child_stmt_key = $child_statement_keys[count($child_statement_keys) - 1];

    return $this->statements[$last_child_stmt_key];
  }

  protected function getFormulaListChildStatement(): ?FormulaListStatement
  {
    $formula_list_stmt = null;
    foreach ($this->statements as $child_stmt) {
      if ($child_stmt instanceof FormulaListStatement) {
        $formula_list_stmt = $child_stmt;
      }
    }

    return $formula_list_stmt;
  }
}
