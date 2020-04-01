<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Custom DQL function returning the difference between two DateTime values in the time unit choose.
 *
 * usage TIME_DIFF(dateTime1, dateTime2, unit)
 *
 * unit can be : ['second' | 'minute' | 'hour' | 'day' | 'month' | 'year']
 */
class TimeDiff extends FunctionNode
{
  /**
   * @var mixed
   */
  public $dateTime1;

  /**
   * @var mixed
   */
  public $dateTime2;

  public string $type;

  /**
   * @throws QueryException
   */
  public function parse(Parser $parser): void
  {
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->dateTime1 = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_COMMA);
    $this->dateTime2 = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_COMMA);
    $this->type = $parser->Literal()->value;
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
  }

  public function getSql(SqlWalker $sqlWalker): string
  {
    switch ($this->type)
    {
      case 'minute':
        $time = 60;
        break;
      case 'hour':
        $time = 3_600;
        break;
      case 'day':
        $time = 86_400;
        break;
      case 'month':
        $time = 2_622_585.6;
        break;
      case 'year':
        $time = 31_471_200;
        break;
      default:
        $time = 1;
    }

    return 'ROUND(TIME_TO_SEC(TIMEDIFF('.
      $this->dateTime1->dispatch($sqlWalker).', '.
      $this->dateTime2->dispatch($sqlWalker).
      '))/'.$time.', 0)';
  }
}
