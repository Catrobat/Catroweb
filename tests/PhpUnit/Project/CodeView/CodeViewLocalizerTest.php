<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeViewLocalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 */
#[CoversClass(CodeViewLocalizer::class)]
class CodeViewLocalizerTest extends TestCase
{
  private CodeViewLocalizer $localizer;

  #[\Override]
  protected function setUp(): void
  {
    $this->localizer = new CodeViewLocalizer(new CodeExportRunner('tests/TestData/CodeExport', new NullLogger()));
  }

  public function testTextWithoutMarkersStaysAsItIs(): void
  {
    $this->assertSame('Cat 1 < 2', $this->localizer->localize('Cat 1 < 2', 'de'));
  }

  public function testStringInTheViewersLanguageWithEnglishFallback(): void
  {
    $this->assertSame('Warte', $this->localizer->localize("\x01s:brick_wait\x02", 'de_DE'));
    $this->assertSame('Wait', $this->localizer->localize("\x01s:brick_wait\x02", 'en'));
    $this->assertSame('Hide', $this->localizer->localize("\x01s:brick_hide\x02", 'de'), 'untranslated: English');
    $this->assertSame('Wait', $this->localizer->localize("\x01s:brick_wait\x02", 'xx'), 'unknown language: English');
  }

  public function testFormatArgumentsAndFormulaTextAroundMarkers(): void
  {
    $this->assertSame('X: 100', $this->localizer->localize("\x01s:x_label\x1f100\x02", 'en'));
    $this->assertSame('touches Warte', $this->localizer->localize("touches \x01s:brick_wait\x02", 'de'));
    $this->assertSame('X: Wait', $this->localizer->localize("\x01s:x_label\x1f\x01s:brick_wait\x02\x02", 'en'), 'marker in an argument');
  }

  public function testPluralsByTheLanguagesRules(): void
  {
    $this->assertSame('second', $this->localizer->localize("\x01p:second_plural\x1f1\x02", 'en'));
    $this->assertSame('seconds', $this->localizer->localize("\x01p:second_plural\x1f2\x02", 'en'));
    $this->assertSame('Sekunden', $this->localizer->localize("\x01p:second_plural\x1f5\x02", 'de'));
  }

  public function testArrayItems(): void
  {
    $this->assertSame('dieses Skript', $this->localizer->localize("\x01a:brick_stop_script_spinner\x1f0\x02", 'de'));
    $this->assertSame('all scripts', $this->localizer->localize("\x01a:brick_stop_script_spinner\x1f1\x02", 'de'));
  }

  public function testUnknownNamesAndStrayControlCharacters(): void
  {
    $this->assertSame('no_such_string', $this->localizer->localize("\x01s:no_such_string\x02", 'en'));
    $this->assertSame('ab', $this->localizer->localize("a\x01b", 'en'));
  }

  public function testAndroidLocales(): void
  {
    $this->assertSame('de', $this->localizer->androidLocale('de_AT'));
    $this->assertSame('de', $this->localizer->androidLocale('de-DE'));
    $this->assertSame('en', $this->localizer->androidLocale('pt_BR'), 'no Portuguese in the test export');
    $this->assertSame('en', $this->localizer->androidLocale('../../etc'));
  }
}
