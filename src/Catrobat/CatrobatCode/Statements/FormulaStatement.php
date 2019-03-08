<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class FormulaStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class FormulaStatement extends Statement
{
  /**
   * @var Statement
   */
  private $leftChild;
  /**
   * @var Statement
   */
  private $rightChild;
  /**
   * @var Statement
   */
  private $type;
  /**
   * @var
   */
  private $category;

  /**
   * FormulaStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $category
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $category)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      "", "");
    $this->category = $category;
  }

  /**
   * @return string
   */
  public function execute()
  {
    $code = $this->executeChildren();

    return $code;
  }

  /**
   * @return string
   */
  public function executeChildren()
  {
    $code = '';
    $endCode = '';

    $this->setVariables();


    if ($this->type != null)
    {
      $code .= $this->type->execute();
    }
    if ($this->type != null && ($this->leftChild != null || $this->rightChild != null))
    {
      $code .= '(';
      $endCode = ')';
    }

    if ($this->leftChild != null)
    {
      $code .= $this->leftChild->execute();
    }

    if ($this->leftChild != null && $this->rightChild != null)
    {
      $code .= ', ';
    }

    if ($this->rightChild != null)
    {
      $code .= $this->rightChild->execute();
    }

    return $code . $endCode;
  }


  /**
   *
   */
  protected function setVariables()
  {

    foreach ($this->statements as $value)
    {
      if ($value instanceof LeftChildStatement)
      {
        $this->leftChild = $value;
      }
      else
      {
        if ($value instanceof RightChildStatement)
        {
          $this->rightChild = $value;
        }
        else
        {
          if ($value instanceof ValueStatement)
          {
            $this->type = $value;
          }
        }
      }
    }
  }

  /**
   * @return mixed
   */
  public function getCategory()
  {
    return $this->category;
  }

}
