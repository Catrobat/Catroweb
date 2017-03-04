<?php

namespace DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * Custom DQL function returning the DateTime value minus the interval choose
 *
 * usage TIME_SUB(dateTime, interval, unit)
 *
 * returning DateTime
 */
class TimeSub extends FunctionNode
{
    /**
     * @var string
     */
    public $dateTime;

    /**
     * @var string
     */
    public $interval;

    /**
     * @var string
     */
    public $unit;

    public function parse(Parser $parser)
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

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'DATE_SUB(' .
        $this->dateTime->dispatch($sqlWalker) . ', INTERVAL ' .
        $this->interval . ' ' .
        strtoupper($this->unit) . ')';
    }
}