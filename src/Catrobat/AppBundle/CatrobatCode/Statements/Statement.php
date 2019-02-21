<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

use Catrobat\AppBundle\CatrobatCode\StatementFactory;
use Symfony\Component\DomCrawler\Form;

/**
 * Class Statement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class Statement
{
  /**
   * @var
   */
  protected $xmlTree;
  /**
   * @var array
   */
  protected $statements;
  /**
   * @var
   */
  protected $spaces;
  /**
   * @var
   */
  private $beginString;
  /**
   * @var
   */
  private $endString;

  /**
   * Statement constructor.
   *
   * @param StatementFactory $statementFactory
   * @param                  $xmlTree
   * @param                  $spaces
   * @param                  $beginString
   * @param                  $endString
   */
  public function __construct(StatementFactory $statementFactory, $xmlTree, $spaces, $beginString, $endString)
  {
    $this->statements = [];
    $this->xmlTree = $xmlTree;
    $this->beginString = $beginString;
    $this->endString = $endString;
    $this->spaces = $spaces;

    $this->createChildren($statementFactory);
  }

  /**
   * @param StatementFactory $statementFactory
   */
  protected function createChildren(StatementFactory $statementFactory)
  {
    if ($this->xmlTree != null)
    {
      $this->addAllScripts($statementFactory->createStatement($this->xmlTree, $this->spaces + 1));
    }
  }

  /**
   * @param $statementsToAdd
   */
  protected function addAllScripts($statementsToAdd)
  {
    foreach ($statementsToAdd as $statement)
    {
      $this->statements[] = $statement;
    }
  }

  /**
   * @return string
   */
  public function execute()
  {
    $code = $this->addSpaces() . $this->beginString . $this->executeChildren() . $this->endString;

    return $code;
  }

  /**
   * @param int $offset
   *
   * @return string
   */
  protected function addSpaces($offset = 0)
  {
    $stringSpaces = "";
    for ($i = 0; $i < ($this->spaces + $offset) * 4; $i++)
    {
      $stringSpaces .= "&nbsp;";
    }

    return $stringSpaces;
  }

  /**
   * @return string
   */
  public function executeChildren()
  {
    $code = '';

    foreach ($this->statements as $value)
    {
      $code .= $value->execute();
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getSpacesForNextBrick()
  {
    return $this->spaces;
  }

  /**
   * @return array
   */
  public function getStatements()
  {
    return $this->statements;
  }

  /**
   * @return mixed
   */
  public function getBeginString()
  {
    return $this->beginString;
  }

  /**
   * @return mixed
   */
  public function getEndString()
  {
    return $this->endString;
  }

  /**
   * @return mixed
   */
  public function getXmlTree()
  {
    return $this->xmlTree;
  }

  /**
   * @return string
   */
  public function getClassName()
  {
    return static::class;
  }

  /**
   * @return mixed
   */
  protected function getLastChildStatement()
  {
    $child_statement_keys = array_keys($this->statements);
    $last_child_stmt_key = $child_statement_keys[sizeof($child_statement_keys) - 1];

    return $this->statements[$last_child_stmt_key];
  }

  /**
   * @return FormulaListStatement|mixed|null
   */
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
