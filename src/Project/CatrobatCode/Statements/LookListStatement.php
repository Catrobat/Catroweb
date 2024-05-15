<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class LookListStatement extends BaseListStatement
{
  final public const string BEGIN_STRING = 'used looks: <br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }
}
