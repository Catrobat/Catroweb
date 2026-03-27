<?php

declare(strict_types=1);

namespace App\Project\Remix;

class RemixUrlParsingState
{
  final public const int STARTING = 0;

  final public const int BETWEEN = 1;

  final public const int TOKEN = 2;
}
