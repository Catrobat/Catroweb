<?php

declare(strict_types=1);

namespace Tests\PhpUnit\DB\Entity;

use App\DB\Entity\FeaturedBanner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FeaturedBanner::class)]
class FeaturedBannerLocalesTest extends TestCase
{
  #[DataProvider('localeProvider')]
  public function testNormalizeLocale(string $input, ?string $expected): void
  {
    $this->assertSame($expected, FeaturedBanner::normalizeLocale($input));
  }

  /**
   * @return iterable<string, array{string, ?string}>
   */
  public static function localeProvider(): iterable
  {
    yield 'language' => ['DE', 'de'];
    yield 'dash locale' => ['de-at', 'de_AT'];
    yield 'underscore locale' => ['de_AT', 'de_AT'];
    yield 'accept-language list' => ['fr-CA,fr;q=0.9,en;q=0.8', 'fr_CA'];
    yield 'three-letter language' => ['ast', 'ast'];
    yield 'script subtag' => ['sr_Latn', 'sr'];
    yield 'numeric region' => ['es-419,es;q=0.9', 'es'];
    yield 'script and region' => ['zh-Hant-TW', 'zh'];
    yield 'garbage' => ['"de"', null];
    yield 'empty' => ['', null];
    yield 'wildcard' => ['*', null];
  }

  public function testSetLocalesNormalizesDeduplicatesAndTreatsEmptyAsEveryone(): void
  {
    $banner = new FeaturedBanner();

    $banner->setLocales(['de-AT', 'de_at', 'FR', 'nonsense!']);
    $this->assertSame(['de_AT', 'fr'], $banner->getLocales());

    $banner->setLocales([]);
    $this->assertNull($banner->getLocales());
  }
}
