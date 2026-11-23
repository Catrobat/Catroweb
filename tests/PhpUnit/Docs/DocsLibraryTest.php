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
    $this->assertSame(['de_DE', 'de-DE', 'de', 'en'], DocsLibrary::locales('de_DE'));
    $this->assertSame(['de', 'en'], DocsLibrary::locales('de'));
    $this->assertSame(['en'], DocsLibrary::locales('en'));
  }

  public function testFallbackOnlyWhenEnglishStandsIn(): void
  {
    $english = new DocPage(DocPage::GUIDE, 'index', 'en', 'en/index.md');
    $this->assertTrue(DocsLibrary::isFallback($english, 'de_DE'));
    $this->assertFalse(DocsLibrary::isFallback($english, 'en_GB'));
    $this->assertFalse(DocsLibrary::isFallback(new DocPage(DocPage::GUIDE, 'index', 'de', 'de/index.md'), 'de_DE'));
  }

  public function testNavigationTreeInBestLocaleWithFlavourFirst(): void
  {
    $row = static fn (string $path, string $locale, string $title, int $order, array $flavors = []): array => ['path' => $path, 'locale' => $locale, 'title' => $title, 'sort_order' => $order, 'flavors' => $flavors, 'updated_at' => new \DateTime()];
    $pages = $this->createStub(DocPageRepository::class);
    $pages->method('guideSummaries')->willReturn([
      $row('index', 'en', 'Docs', 0),
      $row('index', 'de', 'Doku', 0),
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
