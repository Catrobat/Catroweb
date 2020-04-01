<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Custom DQL function returning the DateTime value minus the interval choose.
 *
 * usage TIME_SUB(dateTime, interval, unit)
 *
 * returning DateTime
 */
class TimeSub extends FunctionNode
{
  /**
   * @var mixed
   */
  public $dateTime;

  public string $interval;

  public string $unit;

  /**
   * @throws QueryException
   */
  public function parse(Parser $parser): void
  {
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->dateTime = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_COMMA);
    $this->interval = $parser->Literal()->value;
    $parser->match(Lexer::T_COMMA);
    $this->unit = $parser->Literal()->value;
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
  }

  public function getSql(SqlWalker $sqlWalker): string
  {
    return 'DATE_SUB('.
      $this->dateTime->dispatch($sqlWalker).', INTERVAL '.
      $this->interval.' '.
      strtoupper($this->unit).')';
  }
}
