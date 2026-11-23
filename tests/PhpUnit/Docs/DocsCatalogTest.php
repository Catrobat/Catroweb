<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\Api\DocsApi;
use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\CodeView\CodeViewLocalizer;
use OpenAPI\Server\Model\DocsCatalogResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The brick reference from tests/TestData/CodeExport/catalog.json, a subset of a real catalog.json, and from the real
 * one where a player build is in var/code-export.
 *
 * @internal
 */
#[CoversClass(DocsCatalog::class)]
#[CoversClass(DocsApi::class)]
final class DocsCatalogTest extends TestCase
{
  private const string FIXTURES = __DIR__.'/../../TestData/CodeExport';

  private const string REAL_EXPORT = __DIR__.'/../../../var/code-export';

  public function testLocalizesTheCatalog(): void
  {
    $catalog = $this->catalog()->catalog('de_DE');

    $motion = array_values(array_filter($catalog['categories'], static fn (array $category): bool => 'category_motion' === $category['key']))[0];
    $this->assertSame('Bewegung', $motion['name']);
    $this->assertSame('motion', $motion['color']);
    $this->assertSame('Sinus', $catalog['formula']['functions'][0]['label']);
    $this->assertNull(array_column($catalog['formula']['operators'], 'label', 'name')['POW'], 'an operator shown only as a sign');
  }

  public function testABrickInItsOwnCategoryFirst(): void
  {
    $brick = $this->catalog()->brick('SetXBrick', 'de');

    $this->assertNotNull($brick);
    $this->assertSame('category_motion', $brick['categories'][0]['key']);
    $this->assertGreaterThan(1, \count($brick['categories']), 'also listed elsewhere, as in the app');
    $this->assertSame('Setze x auf 100', $this->catalog()->title($brick['brick']));
    $this->assertNull($this->catalog()->brick('NoSuchBrick', 'de'));
  }

  public function testEveryBrickIsLocalizedInEveryLanguage(): void
  {
    $this->assertNoMarkers($this->catalog(), self::FIXTURES);
  }

  /** The whole real catalog, where a player build was deployed locally (player/README.md). */
  public function testTheRealCatalog(): void
  {
    if (!is_file(self::REAL_EXPORT.'/catalog.json')) {
      $this->markTestSkipped('No player build in var/code-export');
    }
    $catalog = $this->catalog(self::REAL_EXPORT);
    $this->assertGreaterThanOrEqual(200, \count($catalog->types()));
    $this->assertNoMarkers($catalog, self::REAL_EXPORT);
  }

  public function testOldWikiClassNames(): void
  {
    $catalog = $this->catalog();

    $this->assertSame('SetXBrick', $catalog->typeOfClass('SetXBrick'));
    $this->assertSame('StartScript', $catalog->typeOfClass('WhenStartedBrick'));
    $this->assertSame('WhenScript', $catalog->typeOfClass('WhenBrick'));
    $this->assertSame('BroadcastScript', $catalog->typeOfClass('BroadcastReceiverBrick'));
    $this->assertNull($catalog->typeOfClass('NoSuchBrick'));
  }

  public function testRendersBricksLikeTheCodeView(): void
  {
    $catalog = $this->catalog();

    $set_x = $catalog->brick('SetXBrick', 'en');
    $this->assertNotNull($set_x);
    $this->assertSame('<div class="cv-block cv-block--motion">Set x to <span class="cv-field">100</span></div>', $catalog->html($set_x['brick']));

    $start = $catalog->brick('StartScript', 'en');
    $this->assertNotNull($start);
    $this->assertSame('<div class="cv-block cv-block--event"><strong>When scene starts</strong></div>', $catalog->html($start['brick']));

    $if = $catalog->brick('IfLogicBeginBrick', 'en');
    $this->assertNotNull($if);
    $this->assertStringContainsString('<div class="cv-branch-label">Else</div>', $catalog->html($if['brick']));

    $this->assertSame('<div class="cv-block cv-block--unknown">&lt;b&gt;</div>', $catalog->html(['type' => 'X', 'category' => 'nope', 'parts' => [['kind' => 'label', 'text' => '<b>']]]));
    $this->assertSame('data', $catalog->colorClass('uservariables'), 'the code view\'s own mapping');
  }

  public function testNoCatalogDeployedIsAnEmptyReference(): void
  {
    $catalog = $this->catalog(sys_get_temp_dir().'/no-code-export-here');

    $this->assertSame([], $catalog->catalog('en')['categories']);
    $this->assertSame([], $catalog->types());
    $this->assertNull($catalog->brick('SetXBrick', 'en'));
    $this->assertNull($catalog->formula('SIN', 'en'));
    $this->assertNull($catalog->typeOfClass('WhenStartedBrick'));

    $api = new DocsApi($this->createStub(DocsLibrary::class), $this->createStub(DocsSync::class), new RequestStack(), $catalog);
    $code = 0;
    $headers = [];
    $api->docsReferenceBricksTypeGet('SetXBrick', 'en', $code, $headers);
    $this->assertSame(404, $code, 'a brick page of an empty reference is not found, not an error');
    $response = $api->docsReferenceCatalogGet('en', $code, $headers);
    $this->assertSame(200, $code);
    $this->assertInstanceOf(DocsCatalogResponse::class, $response);
    $this->assertSame([], $response->getCategories());
  }

  public function testCatalogTextIsEscaped(): void
  {
    $dir = sys_get_temp_dir().'/docs-catalog-test-'.bin2hex(random_bytes(4));
    mkdir($dir.'/strings', 0o777, true);
    $write = static fn (string $label): int|false => file_put_contents($dir.'/catalog.json', json_encode(['categories' => [[
      'key' => 'category_motion', 'name' => '<i>Motion</i>',
      'bricks' => [['type' => 'SetXBrick', 'category' => 'motion', 'parts' => [['kind' => 'label', 'text' => $label], ['kind' => 'formula', 'text' => '1 < 2']]]],
    ]], 'formula' => []]));
    $write('<script>alert(1)</script>');

    try {
      $catalog = $this->catalog($dir);
      $brick = $catalog->brick('SetXBrick', 'en');
      $this->assertNotNull($brick);
      $html = $catalog->html($brick['brick']);
      $this->assertStringNotContainsString('<script>', $html);
      $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; <span class="cv-field">1 &lt; 2</span>', $html);
      $this->assertSame('<i>Motion</i>', $brick['categories'][0]['name'], 'plain text, escaped where it is shown (Twig, JSON)');

      // A newly deployed catalog is seen by a long-running worker without reset().
      $write('Set x to');
      $this->assertSame('Set x to 1 < 2', $catalog->title((array) $catalog->brick('SetXBrick', 'en')['brick']));
    } finally {
      array_map(unlink(...), glob($dir.'/*.json') ?: []);
      rmdir($dir.'/strings');
      rmdir($dir);
    }
  }

  private function assertNoMarkers(DocsCatalog $catalog, string $export_dir): void
  {
    // Every string the catalog names is one of Catroid's: none falls back to its bare name.
    $english = json_decode((string) file_get_contents($export_dir.'/strings/en.json'), true, 16, JSON_THROW_ON_ERROR);
    $raw = json_decode((string) file_get_contents($export_dir.'/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
    $texts = '';
    array_walk_recursive($raw, static function (mixed $value) use (&$texts): void {
      $texts .= \is_string($value) ? $value : '';
    });
    preg_match_all('/\x01([spa]):([^\x01\x02\x1f]*)/', $texts, $markers, PREG_SET_ORDER);
    $this->assertNotEmpty($markers);
    foreach ($markers as [, $kind, $name]) {
      $this->assertArrayHasKey($name, $english[$kind], 'string '.$kind.':'.$name.' is not in strings/en.json');
    }

    foreach (['en', 'de_DE'] as $locale) {
      $localized = json_encode($catalog->catalog($locale), JSON_THROW_ON_ERROR);
      $this->assertDoesNotMatchRegularExpression('/\\\u000[12]|\\\u001f/', $localized, 'a marker left in '.$locale);
      foreach ($catalog->types() as $type) {
        $brick = $catalog->brick($type, $locale);
        $this->assertNotNull($brick);
        $this->assertDoesNotMatchRegularExpression('/[\x01\x02\x1f]/', $catalog->html($brick['brick']));
      }
    }
  }

  private function catalog(string $export_dir = self::FIXTURES): DocsCatalog
  {
    $runner = new CodeExportRunner($export_dir, new NullLogger());
    $localizer = new CodeViewLocalizer($runner);
    $tree = new CodeTreeBuilder($this->createStub(CodeViewExportStore::class), $localizer, new RequestStack());

    return new DocsCatalog($runner, $localizer, $tree, new ArrayAdapter(), __DIR__.'/../../../assets/Project/CodeViewCategories.json');
  }
}
