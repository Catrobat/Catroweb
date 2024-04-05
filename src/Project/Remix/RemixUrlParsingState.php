<?php

declare(strict_types=1);

namespace App\Project\Remix;

class RemixUrlParsingState
{
  /**
   * @var int
   */
  final public const STARTING = 0;
  /**
   * @var int
   */
  final public const BETWEEN = 1;
  /**
   * @var int
   */
  final public const TOKEN = 2;
}
