<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

class FormulaResolver
{
  public static function resolve(\SimpleXMLElement $formula_list): array
  {
    $formulas = [];
    foreach ($formula_list->children() as $formula) {
      $formulas[(string) $formula[Constants::CATEGORY_ATTRIBUTE]] = (new FormulaResolver())->resolveFormula($formula);
    }

    return $formulas;
  }

  private function resolveFormula(?\SimpleXMLElement $formula): ?string
  {
    if ($formula instanceof \SimpleXMLElement) {
      return match ((string) $formula->type) {
        Constants::OPERATOR_FORMULA_TYPE => (new FormulaResolver())->resolveFormula($formula->leftChild)
          .' '.(new FormulaResolver())->resolveOperator($formula->value)
          .' '.(new FormulaResolver())->resolveFormula($formula->rightChild),
        Constants::FUNCTION_FORMULA_TYPE => (new FormulaResolver())->resolveFunction($formula),
        Constants::BRACKET_FORMULA_TYPE => '('.(new FormulaResolver())->resolveFormula($formula->rightChild).')',
        default => (string) $formula->value,
      };
    }

    return null;
  }

  private function resolveFunction(mixed $formula): string
  {
    if ('TRUE' == $formula->value) {
      $resolved_function = 'true';
    } elseif ('FALSE' == $formula->value) {
      $resolved_function = 'false';
    } else {
      if (null != $formula->rightChild) {
        $function_input_formula = (new FormulaResolver())->resolveFormula($formula->leftChild)
          .', '.(new FormulaResolver())->resolveFormula($formula->rightChild);
      } else {
        $function_input_formula = (new FormulaResolver())->resolveFormula($formula->leftChild);
      }

      $resolved_function = strtolower((string) $formula->value).'( '.$function_input_formula.' )';
    }

    return $resolved_function;
  }

  private function resolveOperator(\SimpleXMLElement $operator): ?string
  {
    return match ((string) $operator) {
      Constants::PLUS_OPERATOR => '+',
      Constants::MINUS_OPERATOR => '-',
      Constants::MULT_OPERATOR => '*',
      Constants::DIVIDE_OPERATOR => '/',
      Constants::EQUAL_OPERATOR => '=',
      Constants::NOT_EQUAL_OPERATOR => '!=',
      Constants::GREATER_OPERATOR => '>',
      Constants::GREATER_EQUAL_OPERATOR => '>=',
      Constants::SMALLER_OPERATOR => '<',
      Constants::SMALLER_EQUAL_OPERATOR => '<=',
      Constants::NOT_OPERATOR => 'NOT',
      Constants::OR_OPERATOR => 'OR',
      Constants::AND_OPERATOR => 'AND',
      default => null,
    };
  }
}
