<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class WhenScriptStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'when program started <br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }
}
