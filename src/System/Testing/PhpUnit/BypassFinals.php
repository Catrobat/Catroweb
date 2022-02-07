<?php

namespace App\System\Testing\PhpUnit;

/**
 * In Docker there seems to an issue with bypassFinals and the first run of the unit tests.
 * Therefor, we have to start a dummy run before running the real tests.
 *
 * @internal
 * @coversNothing
 */
final class BypassFinals extends DefaultTestCase
{
  public function testNothingButEnableBypassFinals(): void
  {
    $this->assertTrue(true);
  }
}
