<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsLibrary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DocsLibrary::class)]
final class DocsLibraryTest extends TestCase
{
  public function testLocaleCandidates(): void
  {
    $library = new DocsLibrary($this->createStub(DocPageRepository::class));
    $this->assertSame(['de_DE', 'de-DE', 'de', 'en'], $library->locales('de_DE'));
    $this->assertSame(['de', 'de_DE', 'en'], $library->locales('de'));
    $this->assertSame(['de_AT', 'de-AT', 'de', 'de_DE', 'en'], $library->locales('de_AT'));
    $this->assertSame(['pt-BR', 'pt_BR', 'pt', 'en'], $library->locales('pt-BR'));
    $this->assertSame(['zh_TW', 'zh-TW', 'zh', 'zh_CN', 'en'], $library->locales('zh_TW'));
    $this->assertSame(['en'], $library->locales('en'));
    $this->assertSame(['en_GB', 'en-GB', 'en'], $library->locales('en_GB'));
  }

  public function testFallbackOnlyWhenEnglishStandsIn(): void
  {
    $english = new DocPage(DocPage::GUIDE, 'index', 'en', 'en/index.md');
    $this->assertTrue(DocsLibrary::isFallback($english, 'de_DE'));
    $this->assertFalse(DocsLibrary::isFallback($english, 'en_GB'));
    $this->assertFalse(DocsLibrary::isFallback(new DocPage(DocPage::GUIDE, 'index', 'de_DE', 'de_DE/index.md'), 'de_DE'));
  }

  public function testNavigationTreeInBestLocaleWithFlavourFirst(): void
  {
    $row = static fn (string $path, string $locale, string $title, int $order, array $flavors = []): array => ['path' => $path, 'locale' => $locale, 'title' => $title, 'sort_order' => $order, 'flavors' => $flavors, 'updated_at' => new \DateTime()];
    $pages = $this->createStub(DocPageRepository::class);
    $pages->method('guideSummaries')->willReturn([
      $row('index', 'en', 'Docs', 0),
      $row('index', 'de_DE', 'Doku', 0),
      $row('tutorials', 'en', 'Tutorials', 20),
      $row('tutorials/jump', 'en', 'Jump', 30),
      $row('tutorials/stitch', 'en', 'Stitch', 40, ['embroidery']),
      $row('tutorials/deep/page', 'en', 'Deep page', 1), // no tutorials/deep page: hangs under tutorials
      $row('getting-started', 'en', 'Getting started', 10),
    ]);
    $library = new DocsLibrary($pages);

    $navigation = $library->navigation('de', 'embroidery');

    $this->assertSame(['index', 'getting-started', 'tutorials'], array_column($navigation, 'path'));
    $this->assertSame('Doku', $navigation[0]['title']);
    $this->assertSame(['tutorials/stitch', 'tutorials/deep/page', 'tutorials/jump'], array_column($navigation[2]['children'], 'path'));
    $this->assertSame(
      ['index', 'getting-started', 'tutorials', 'tutorials/stitch', 'tutorials/deep/page', 'tutorials/jump'],
      array_column(DocsLibrary::flatten($navigation), 'path'),
    );
    $this->assertSame(['tutorials/deep/page', 'tutorials/jump', 'tutorials/stitch'], array_column($library->navigation('de')[2]['children'], 'path'));
  }
}
