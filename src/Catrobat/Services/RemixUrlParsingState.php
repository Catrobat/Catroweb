<?php

namespace App\Catrobat\Services;

class RemixUrlParsingState
{
  /**
   * @var int
   */
  const STARTING = 0;
  /**
   * @var int
   */
  const BETWEEN = 1;
  /**
   * @var int
   */
  const TOKEN = 2;
}
