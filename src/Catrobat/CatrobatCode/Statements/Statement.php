<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\StatementFactory;
use SimpleXMLElement;

class Statement
{
  protected ?SimpleXMLElement $xmlTree = null;

  protected array $statements = [];

  protected int $spaces;

  private string $beginString;

  private string $endString;

  public function __construct(StatementFactory $statementFactory, ?SimpleXMLElement $xmlTree, int $spaces, string $beginString, string $endString)
  {
    $this->statements = [];
    $this->xmlTree = $xmlTree;
    $this->beginString = $beginString;
    $this->endString = $endString;
    $this->spaces = $spaces;

    $this->createChildren($statementFactory);
  }

  public function execute(): string
  {
    return $this->addSpaces().$this->beginString.$this->executeChildren().$this->endString;
  }

  public function executeChildren(): string
  {
    $code = '';

    foreach ($this->statements as $value)
    {
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

  public function getXmlTree(): ?SimpleXMLElement
  {
    return $this->xmlTree;
  }

  public function getClassName(): string
  {
    return static::class;
  }

  protected function createChildren(StatementFactory $statementFactory): void
  {
    if (null != $this->xmlTree)
    {
      $this->addAllScripts($statementFactory->createStatement($this->xmlTree, $this->spaces + 1));
    }
  }

  /**
   * @param Statement[] $statementsToAdd
   */
  protected function addAllScripts(array $statementsToAdd): void
  {
    foreach ($statementsToAdd as $statement)
    {
      $this->statements[] = $statement;
    }
  }

  protected function addSpaces(int $offset = 0): string
  {
    $stringSpaces = '';
    for ($i = 0; $i < ($this->spaces + $offset) * 4; ++$i)
    {
      $stringSpaces .= '&nbsp;';
    }

    return $stringSpaces;
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
    foreach ($this->statements as $child_stmt)
    {
      if ($child_stmt instanceof FormulaListStatement)
      {
        $formula_list_stmt = $child_stmt;
      }
    }

    return $formula_list_stmt;
  }
}
