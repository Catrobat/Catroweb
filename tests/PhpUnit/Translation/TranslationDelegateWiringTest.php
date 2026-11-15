<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\GoogleTranslateApi;
use App\Translation\ItranslateApi;
use App\Translation\TranslationDelegate;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Regression: autowiring skips variadic parameters, so the delegate once got no providers at all and every
 * machine translation answered 503. The test env swaps in FakeTranslationDelegate, which inherits the wiring.
 *
 * @internal
 */
#[CoversNothing]
final class TranslationDelegateWiringTest extends KernelTestCase
{
  public function testTheDelegateGetsEveryTranslationProvider(): void
  {
    $delegate = self::getContainer()->get(TranslationDelegate::class);

    $apis = (new \ReflectionProperty(TranslationDelegate::class, 'apis'))->getValue($delegate);
    \assert(\is_array($apis));

    $this->assertCount(2, $apis);
    $this->assertNotEmpty(array_filter($apis, static fn (object $api): bool => $api instanceof GoogleTranslateApi));
    $this->assertNotEmpty(array_filter($apis, static fn (object $api): bool => $api instanceof ItranslateApi));
  }
}
