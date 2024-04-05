<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class BaseListStatement extends Statement
{
  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces, mixed $start, mixed $end)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $start,
      $end);
  }

  public function execute(): string
  {
    if (count(parent::getStatements()) < 1) {
      return '';
    }

    return $this->addSpaces().parent::getBeginString().$this->executeChildren().parent::getEndString();
  }

  public function executeChildren(): string
  {
    $code = '';
    $spacesString = parent::addSpaces(1);
    foreach ($this->statements as $value) {
      $code .= $spacesString.$value->execute().'<br/>';
    }

    return $code;
  }
}
