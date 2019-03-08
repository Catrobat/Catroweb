<?php

namespace App\Catrobat\CatrobatCode\Statements;

/**
 * Class ShowTextStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class ShowTextStatement extends Statement
{
  const BEGIN_STRING = "show variable ";
  const END_STRING = ")<br/>";


  /**
   * ShowTextStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  /**
   * @return string
   */
  public function getBrickText()
  {
    $variable_name = $this->xmlTree->userVariableName;

    foreach ($this->getFormulaListChildStatement()->getStatements() as $statement)
    {
      if ($statement instanceof FormulaStatement)
      {
        switch ($statement->getCategory())
        {
          case 'Y_POSITION':
            $formula_y_pos = $statement->execute();
            break;
          case 'X_POSITION':
            $formula_x_pos = $statement->execute();
            break;
        }
      }
    }
    $formula_x_pos_no_markup = preg_replace("#<[^>]*>#", '', $formula_x_pos);
    $formula_y_pos_no_markup = preg_replace("#<[^>]*>#", '', $formula_y_pos);

    return "Show variable " . $variable_name . " at X: " . $formula_x_pos_no_markup . " Y: " . $formula_y_pos_no_markup;
  }

  /**
   * @return string
   */
  public function getBrickColor()
  {
    return "1h_brick_red.png";
  }

}
