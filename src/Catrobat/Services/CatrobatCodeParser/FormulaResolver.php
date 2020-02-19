<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

/**
 * Class FormulaResolver.
 */
class FormulaResolver
{
  /**
   * @param SimpleXMLElement $formula_list
   *
   * @return array
   */
  public static function resolve($formula_list)
  {
    $formulas = [];
    foreach ($formula_list->children() as $formula)
    {
      $formulas[(string) $formula[Constants::CATEGORY_ATTRIBUTE]] = FormulaResolver::resolveFormula($formula);
    }

    return $formulas;
  }

  /**
   * @param $formula
   *
   * @return string|null
   */
  private static function resolveFormula($formula)
  {
    $resolved_formula = null;
    if (null != $formula)
    {
      switch ($formula->type)
      {
        case Constants::OPERATOR_FORMULA_TYPE:
          $resolved_formula = FormulaResolver::resolveFormula($formula->leftChild)
            .' '.FormulaResolver::resolveOperator($formula->value)
            .' '.FormulaResolver::resolveFormula($formula->rightChild);
          break;
        case Constants::FUNCTION_FORMULA_TYPE:
          $resolved_formula = FormulaResolver::resolveFunction($formula);
          break;
        case Constants::BRACKET_FORMULA_TYPE:
          $resolved_formula = '('.FormulaResolver::resolveFormula($formula->rightChild).')';
          break;
        default:
          $resolved_formula = (string) $formula->value;
          break;
      }
    }

    return $resolved_formula;
  }

  /**
   * @param $formula
   *
   * @return string|null
   */
  private static function resolveFunction($formula)
  {
    $resolved_function = null;

    if ('TRUE' == $formula->value)
    {
      $resolved_function = 'true';
    }
    else
    {
      if ('FALSE' == $formula->value)
      {
        $resolved_function = 'false';
      }
      else
      {
        if (null != $formula->rightChild)
        {
          $function_input_formula = FormulaResolver::resolveFormula($formula->leftChild)
            .', '.FormulaResolver::resolveFormula($formula->rightChild);
        }
        else
        {
          $function_input_formula = FormulaResolver::resolveFormula($formula->leftChild);
        }
        $resolved_function = strtolower($formula->value).'( '.$function_input_formula.' )';
      }
    }

    return $resolved_function;
  }

  /**
   * @param $operator
   *
   * @return string|null
   */
  private static function resolveOperator($operator)
  {
    $resolved_operator = null;
    switch ($operator)
    {
      case Constants::PLUS_OPERATOR:
        $resolved_operator = '+';
        break;
      case Constants::MINUS_OPERATOR:
        $resolved_operator = '-';
        break;
      case Constants::MULT_OPERATOR:
        $resolved_operator = '*';
        break;
      case Constants::DIVIDE_OPERATOR:
        $resolved_operator = '/';
        break;
      case Constants::EQUAL_OPERATOR:
        $resolved_operator = '=';
        break;
      case Constants::NOT_EQUAL_OPERATOR:
        $resolved_operator = '!=';
        break;
      case Constants::GREATER_OPERATOR:
        $resolved_operator = '>';
        break;
      case Constants::GREATER_EQUAL_OPERATOR:
        $resolved_operator = '>=';
        break;
      case Constants::SMALLER_OPERATOR:
        $resolved_operator = '<';
        break;
      case Constants::SMALLER_EQUAL_OPERATOR:
        $resolved_operator = '<=';
        break;
      case Constants::NOT_OPERATOR:
        $resolved_operator = 'NOT';
        break;
      case Constants::OR_OPERATOR:
        $resolved_operator = 'OR';
        break;
      case Constants::AND_OPERATOR:
        $resolved_operator = 'AND';
        break;
      default:
        $resolved_operator = null;
        break;
    }

    return $resolved_operator;
  }
}
