<?php

namespace App\System\Testing\PhpUnit\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

final class BypassFinalHook implements BeforeTestHook
{
  public function executeBeforeTest(string $test): void
  {
    BypassFinals::enable();
  }
}
