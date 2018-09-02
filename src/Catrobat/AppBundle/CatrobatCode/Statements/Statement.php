<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\StatementFactory;
use Symfony\Component\DomCrawler\Form;

class Statement
{
  protected $xmlTree;
  protected $statements;
  protected $spaces;
  private $beginString;
  private $endString;

  public function __construct(StatementFactory $statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $this->statements = [];
    $this->xmlTree = $xmlTree;
    $this->beginString = $beginString;
    $this->endString = $endString;
    $this->spaces = $spaces;

    $this->createChildren($statementFactory);
  }

  protected function createChildren(StatementFactory $statementFactory)
  {
    if ($this->xmlTree != null)
    {
      $this->addAllScripts($statementFactory->createStatement($this->xmlTree, $this->spaces + 1));
    }
  }

  protected function addAllScripts($statementsToAdd)
  {
    foreach ($statementsToAdd as $statement)
    {
      $this->statements[] = $statement;
    }
  }

  public function execute()
  {
    $code = $this->addSpaces() . $this->beginString . $this->executeChildren() . $this->endString;

    return $code;
  }

  protected function addSpaces($offset = 0)
  {
    $stringSpaces = "";
    for ($i = 0; $i < ($this->spaces + $offset) * 4; $i++)
    {
      $stringSpaces .= "&nbsp;";
    }

    return $stringSpaces;
  }

  public function executeChildren()
  {
    $code = '';

    foreach ($this->statements as $value)
    {
      $code .= $value->execute();
    }

    return $code;
  }

  public function getSpacesForNextBrick()
  {
    return $this->spaces;
  }

  public function getStatements()
  {
    return $this->statements;
  }

  public function getBeginString()
  {
    return $this->beginString;
  }

  public function getEndString()
  {
    return $this->endString;
  }

  public function getXmlTree()
  {
    return $this->xmlTree;
  }

  public function getClassName()
  {
    return static::class;
  }

  protected function getLastChildStatement()
  {
    $child_statement_keys = array_keys($this->statements);
    $last_child_stmt_key = $child_statement_keys[sizeof($child_statement_keys) - 1];

    return $this->statements[$last_child_stmt_key];
  }

  protected function getFormulaListChildStatement()
  {
    $formula_list_stmt = null;
    foreach ($this->statements as $child_stmt)
      if ($child_stmt instanceof FormulaListStatement)
      {
        $formula_list_stmt = $child_stmt;
      }

    return $formula_list_stmt;
  }
}

?>
