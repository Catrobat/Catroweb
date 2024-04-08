<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class FormulaStatement extends Statement
{
  private ?LeftChildStatement $leftChild = null;

  private ?RightChildStatement $rightChild = null;

  private ?ValueStatement $type = null;

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, private readonly mixed $category)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces, '', '');
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

    if (null != $this->type) {
      $code .= $this->type->execute();
    }
    if (null != $this->type && (null != $this->leftChild || null != $this->rightChild)) {
      $code .= '(';
      $endCode = ')';
    }

    if (null != $this->leftChild) {
      $code .= $this->leftChild->execute();
    }

    if (null != $this->leftChild && null != $this->rightChild) {
      $code .= ', ';
    }

    if (null != $this->rightChild) {
      $code .= $this->rightChild->execute();
    }

    return $code.$endCode;
  }

  public function getCategory(): mixed
  {
    return $this->category;
  }

  protected function setVariables(): void
  {
    foreach ($this->statements as $value) {
      if ($value instanceof LeftChildStatement) {
        $this->leftChild = $value;
      } elseif ($value instanceof RightChildStatement) {
        $this->rightChild = $value;
      } elseif ($value instanceof ValueStatement) {
        $this->type = $value;
      }
    }
  }
}
