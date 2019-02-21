<?php

namespace DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * Custom DQL function returning the difference between two DateTime values in the time unit choose
 *
 * usage TIME_DIFF(dateTime1, dateTime2, unit)
 *
 * unit can be : ['second' | 'minute' | 'hour' | 'day' | 'month' | 'year']
 */
class TimeDiff extends FunctionNode
{
  /**
   * @var string
   */
  public $dateTime1;

  /**
   * @var string
   */
  public $dateTime2;

  /**
   * @var string
   */
  public $type;

  /**
   * @param Parser $parser
   *
   * @throws \Doctrine\ORM\Query\QueryException
   */
  public function parse(Parser $parser)
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

  /**
   * @param SqlWalker $sqlWalker
   *
   * @return string
   */
  public function getSql(SqlWalker $sqlWalker)
  {
    switch ($this->type)
    {
      case "minute":
        $time = 60;
        break;
      case "hour":
        $time = 3600;
        break;
      case "day":
        $time = 86400;
        break;
      case "month":
        $time = 2622585.6;
        break;
      case "year":
        $time = 31471200;
        break;
      default:
        $time = 1;
    }

    return 'ROUND(TIME_TO_SEC(TIMEDIFF(' .
      $this->dateTime1->dispatch($sqlWalker) . ', ' .
      $this->dateTime2->dispatch($sqlWalker) .
      '))/' . $time . ', 0)';
  }
}