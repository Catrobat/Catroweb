<?php

namespace App\Catrobat\CatrobatCode\Statements;

class FormulaStatement extends Statement
{
  private ?LeftChildStatement $leftChild = null;

  private ?RightChildStatement $rightChild = null;

  private ?ValueStatement $type = null;

  /**
   * @var mixed
   */
  private $category;

  /**
   * FormulaStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $category
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $category)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      '', '');
    $this->category = $category;
  }

  public function execute(): string
  {
    return $this->executeChildren();
  }

  public function executeChildren(): string
  {
    $code = '';
    $endCode = '';

    $this->setVariables();

    if (null != $this->type)
    {
      $code .= $this->type->execute();
    }
    if (null != $this->type && (null != $this->leftChild || null != $this->rightChild))
    {
      $code .= '(';
      $endCode = ')';
    }

    if (null != $this->leftChild)
    {
      $code .= $this->leftChild->execute();
    }

    if (null != $this->leftChild && null != $this->rightChild)
    {
      $code .= ', ';
    }

    if (null != $this->rightChild)
    {
      $code .= $this->rightChild->execute();
    }

    return $code.$endCode;
  }

  /**
   * @return mixed
   */
  public function getCategory()
  {
    return $this->category;
  }

  protected function setVariables(): void
  {
    foreach ($this->statements as $value)
    {
      if ($value instanceof LeftChildStatement)
      {
        $this->leftChild = $value;
      }
      elseif ($value instanceof RightChildStatement)
      {
        $this->rightChild = $value;
      }
      elseif ($value instanceof ValueStatement)
      {
        $this->type = $value;
      }
    }
  }
}
