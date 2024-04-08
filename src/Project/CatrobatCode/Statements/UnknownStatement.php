<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class UnknownStatement extends Statement
{
  /**
   * @var string
   */
  final public const BEGIN_STRING = 'Unknown Statement';
  /**
   * @var string
   */
  final public const END_STRING = '<br/>';

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      self::END_STRING);
  }

  public function getBrickText(): string
  {
    return 'Unknown Brick';
  }

  public function getBrickColor(): string
  {
    return '1h_brick_grey.png';
  }
}
