<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class SoundStatement.
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
      '');
  }

  /**
   * @return string
   */
  public function execute()
  {
    $code = $this->value;
    $this->findNames();

    if (null != $this->name)
    {
      $code .= $this->name->execute();
    }

    if (null != $this->fileName)
    {
      $code .= ' (filename: '.$this->fileName->execute().')';
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  private function findNames()
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if (null != $statement)
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
}
