<?php

namespace App\Project\CatrobatCode\Statements;

class GlideToStatement extends Statement
{
  /**
   * @var string
   */
  public const BEGIN_STRING = 'glide (';
  /**
   * @var string
   */
  public const END_STRING = ')<br/>';

  /**
   * GlideToStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
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
