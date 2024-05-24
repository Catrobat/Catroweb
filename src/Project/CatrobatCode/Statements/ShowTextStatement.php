<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

use App\Project\CatrobatCode\StatementFactory;

class ShowTextStatement extends Statement
{
  final public const string BEGIN_STRING = 'show variable ';

  final public const string END_STRING = ')<br/>';

  public function __construct(StatementFactory $statementFactory, ?\SimpleXMLElement $xmlTree, int $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    $variable_name = $this->xmlTree->userVariableName;

    $formula_x_pos = '';
    $formula_y_pos = '';

    foreach ($this->getFormulaListChildStatement()->getStatements() as $statement) {
      if ($statement instanceof FormulaStatement) {
        if ('Y_POSITION' == $statement->getCategory()) {
          $formula_y_pos = $statement->execute();
        } elseif ('X_POSITION' == $statement->getCategory()) {
          $formula_x_pos = $statement->execute();
        }
      }
    }

    $formula_x_pos_no_markup = preg_replace('#<[^>]*>#', '', $formula_x_pos);
    $formula_y_pos_no_markup = preg_replace('#<[^>]*>#', '', $formula_y_pos);

    return 'Show variable '.$variable_name.' at X: '.$formula_x_pos_no_markup.' Y: '.$formula_y_pos_no_markup;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_red.png';
  }
}
