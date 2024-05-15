<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class GlideToStatement extends Statement
{
  final public const string BEGIN_STRING = 'glide (';
  final public const string END_STRING = ')<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    $formula_x_dest = '';
    $formula_y_dest = '';
    $formula_duration = '';

    foreach ($this->getFormulaListChildStatement()->getStatements() as $statement) {
      if ($statement instanceof FormulaStatement) {
        switch ($statement->getCategory()) {
          case 'Y_DESTINATION':
            $formula_y_dest = $statement->execute();
            break;
          case 'X_DESTINATION':
            $formula_x_dest = $statement->execute();
            break;
          case 'DURATION_IN_SECONDS':
            $formula_duration = $statement->execute();
            break;
        }
      }
    }

    $formula_x_dest_no_markup = preg_replace('#<[^>]*>#', '', $formula_x_dest);
    $formula_y_dest_no_markup = preg_replace('#<[^>]*>#', '', $formula_y_dest);
    $formula_duration_no_markup = preg_replace('#<[^>]*>#', '', $formula_duration);

    return 'Glide '.$formula_duration_no_markup.' second(s) to X: '.$formula_x_dest_no_markup.' Y: '.$formula_y_dest_no_markup;
  }

  public function getBrickColor(): string
  {
    return '1h_brick_blue.png';
  }
}
