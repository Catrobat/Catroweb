<?php

declare(strict_types=1);

namespace App\Project\Remix;

class RemixUrlParsingState
{
  /**
   * @var int
   */
  final public const int STARTING = 0;

  /**
   * @var int
   */
  final public const int BETWEEN = 1;

  /**
   * @var int
   */
  final public const int TOKEN = 2;
}
