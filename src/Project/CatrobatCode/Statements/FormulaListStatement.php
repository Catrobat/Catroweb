<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class FormulaListStatement extends Statement
{
  final public const string X_POSITION = 'X_POSITION';

  final public const string Y_POSITION = 'Y_POSITION';

  private ?FormulaStatement $x_position = null;

  private ?FormulaStatement $y_position = null;

  public function __construct(mixed $statement_factory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statement_factory, $xmlTree, $spaces - 1,
      '', '');
  }

  #[\Override]
  public function executeChildren(): string
  {
    $code = '';
    $counter = 0;

    $statementCount = count($this->statements);
    foreach ($this->statements as $value) {
      ++$counter;

      $code .= $value->execute();
      if ($counter < $statementCount) {
        $code .= ', ';
      }
    }

    return $code;
  }

  public function executePlaceAtFormula(): string
  {
    $code = '';
    $endCode = '';

    $this->setVariables();

    if (null != $this->x_position) {
      $code .= 'X('.$this->x_position->execute().')';
    }

    if (null != $this->x_position && null != $this->y_position) {
      $code .= ', ';
    }

    if (null != $this->y_position) {
      $code .= 'Y('.$this->y_position->execute().')';
    }

    return $code.$endCode;
  }

  protected function setVariables(): void
  {
    foreach ($this->statements as $value) {
      if ($value instanceof FormulaStatement) {
        if (self::X_POSITION == $value->getCategory()) {
          $this->x_position = $value;
        } elseif (self::Y_POSITION == $value->getCategory()) {
          $this->y_position = $value;
        }
      }
    }
  }
}
