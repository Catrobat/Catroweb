<?php

namespace App\Catrobat\Services;

class RemixUrlParsingState
{
  /**
   * @var int
   */
  public const STARTING = 0;
  /**
   * @var int
   */
  public const BETWEEN = 1;
  /**
   * @var int
   */
  public const TOKEN = 2;
}
