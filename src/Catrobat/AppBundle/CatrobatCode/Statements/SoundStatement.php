<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class SoundStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class SoundStatement extends Statement
{
  /**
   * @var
   */
  private $value;
  /**
   * @var
   */
  private $fileName;
  /**
   * @var
   */
  private $name;

  /**
   * SoundStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

  /**
   * @return string
   */
  public function execute()
  {
    $code = $this->value;
    $this->findNames();

    if ($this->name != null)
    {
      $code .= $this->name->execute();
    }

    if ($this->fileName != null)
    {
      $code .= ' (filename: ' . $this->fileName->execute() . ')';
    }

    return $code;
  }

  /**
   *
   */
  private function findNames()
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if ($statement != null)
      {
        if ($statement instanceof ValueStatement)
        {
          $this->name = $statement;
        }
        else
        {
          if ($statement instanceof FileNameStatement)
          {
            $this->fileName = $statement;
          }
        }
      }
    }
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }
}
