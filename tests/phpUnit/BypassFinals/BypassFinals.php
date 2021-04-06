<?php

namespace Tests\phpUnit\BypassFinals;

use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * In Docker there seems to an issue with bypassFinals and the first run of the unit tests.
 * Therefore we have to start a dummy run before running the real tests.
 *
 * @internal
 * @coversNothing
 */
final class BypassFinals extends CatrowebTestCase
{
  public function testNothingButEnableBypassFinals(): void
  {
    $this->assertTrue(true);
  }
}
