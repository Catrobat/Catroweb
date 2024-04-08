<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class PlaceAtStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'place at ';
  /**
   * @var string
   */
  final public const END_STRING = '<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function executeChildren(): string
  {
    $code = '';

    foreach ($this->statements as $value) {
      if ($value instanceof FormulaListStatement) {
        $code .= $value->executePlaceAtFormula();
      }
    }

    return $code;
  }

  public function getBrickText(): string
  {
    $formula_x_dest = '';
    $formula_y_dest = '';

    foreach ($this->getFormulaListChildStatement()->getStatements() as $statement) {
      if ($statement instanceof FormulaStatement) {
        if ('Y_POSITION' == $statement->getCategory()) {
          $formula_y_dest = $statement->execute();
        } elseif ('X_POSITION' == $statement->getCategory()) {
          $formula_x_dest = $statement->execute();
        }
      }
    }

    $formula_x_dest_no_markup = preg_replace('#<[^>]*>#', '', $formula_x_dest);
    $formula_y_dest_no_markup = preg_replace('#<[^>]*>#', '', $formula_y_dest);

    return 'Place at X: '.$formula_x_dest_no_markup.' Y: '.$formula_y_dest_no_markup;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
