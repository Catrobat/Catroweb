<?php

namespace App\Catrobat\CatrobatCode\Statements;

use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;

/**
 * Class LookStatement.
 */
class LookStatement extends Statement
{
  /**
   * @var
   */
  private $value;
  /**
   * @var Statement
   */
  private $fileName;

  /**
   * LookStatement constructor.
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
    $this->findNames();

    $code = '';

    if (null != $this->value)
    {
      $code = SyntaxHighlightingConstants::VARIABLES.$this->value.SyntaxHighlightingConstants::END;
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
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @return mixed
   */
  public function getFileName()
  {
    return $this->fileName;
  }

  private function findNames()
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if (null != $statement)
      {
        if ($statement instanceof FileNameStatement)
        {
          $this->fileName = $statement;
        }
      }
    }
  }
}
