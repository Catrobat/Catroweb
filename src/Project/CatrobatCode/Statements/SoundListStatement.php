<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class SoundListStatement extends BaseListStatement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'used sounds: <br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }
}
