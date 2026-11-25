<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\Docs\DocsCatalog;
use App\Docs\DocsMarkdown;
use App\Docs\Markdown\EmbedParser;
use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\CodeView\CodeViewLocalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(DocsMarkdown::class)]
#[CoversClass(EmbedParser::class)]
final class DocsMarkdownTest extends TestCase
{
  private const string FIXTURES = __DIR__.'/../../TestData/Docs/';

  /** @param array{kind: string, locale: string, path: string}|null $expected */
  #[DataProvider('files')]
  public function testLocatesRepoFiles(string $file, ?array $expected): void
  {
    $this->assertSame($expected, DocsMarkdown::locate($file));
  }

  /** @return array<string, array{string, array{kind: string, locale: string, path: string}|null}> */
  public static function files(): array
  {
    return [
      'root index' => ['en/index.md', ['kind' => 'guide', 'locale' => 'en', 'path' => 'index']],
      'folder index' => ['de_DE/tutorials/index.md', ['kind' => 'guide', 'locale' => 'de_DE', 'path' => 'tutorials']],
      'page' => ['pt-BR/tutorials/jump.md', ['kind' => 'guide', 'locale' => 'pt-BR', 'path' => 'tutorials/jump']],
      'brick' => ['reference/bricks/SetXBrick.md', ['kind' => 'brick', 'locale' => 'en', 'path' => 'SetXBrick']],
      'translated brick' => ['de/reference/bricks/SetXBrick.md', ['kind' => 'brick', 'locale' => 'de', 'path' => 'SetXBrick']],
      'readme' => ['README.md', null],
      'media' => ['media/tutorials/jump.png', null],
      'scripts' => ['scripts/notes.md', null],
      'traversal' => ['en/../secret.md', null],
      'underscore segment' => ['en/_media/page.md', null],
    ];
  }

  public function testRelativeUrlsWorkUnderEveryTheme(): void
  {
    // Page /T/docs resolves against /T/, page /T/docs/a/b against /T/docs/a/.
    $this->assertSame('docs/tutorials', DocsMarkdown::relativeUrl('docs', 'docs/tutorials'));
    $this->assertSame('../tutorials', DocsMarkdown::relativeUrl('docs/tutorials/jump', 'docs/tutorials'));
    $this->assertSame('../../docs', DocsMarkdown::relativeUrl('docs/tutorials/jump', 'docs'));
    $this->assertSame('tutorials/jump', DocsMarkdown::relativeUrl('docs/tutorials', 'docs/tutorials/jump'));
    $this->assertSame('../../project/42', DocsMarkdown::relativeUrl('docs/tutorials/jump', 'project/42'));
  }

  public function testCarriesAnExplicitLocaleOnlyToDocsLinks(): void
  {
    $html = <<<'HTML'
      <p><a href="../quick-start-guide/add-actors-and-objects#object">Relative</a>
      <a href="/app/docs/reference?mode=compact&amp;view=list#category_motion">Root-relative</a>
      <a href="https://share.catrobat.org/app/docs">Same site</a>
      <a href="?view=full#heading">Same page with a query</a>
      <a href="other?locale=fr">Chosen locale</a>
      <a href="#on-this-page">Jump</a>
      <a href="https://catrobat.org/app/docs">Other site</a>
      <a href="//example.org/app/docs">Other host</a>
      <a href="/app/docs-preview">Similar prefix</a>
      <a href="../../../project/123">Project</a>
      <a href="/docs-media/tutorial.png?v=abc"><img src="/docs-media/tutorial.png?v=abc"></a></p>
      HTML;
    $page = 'https://share.catrobat.org/app/docs/getting-started/elements/medialibrary';
    $this->assertSame($html, DocsMarkdown::withLinkLocale($html, $page, '/app/docs', null));
    $localized = DocsMarkdown::withLinkLocale($html, $page, '/app/docs', 'de_DE');
    $this->assertStringContainsString('href="../quick-start-guide/add-actors-and-objects?locale=de_DE#object"', $localized);
    $this->assertStringContainsString('href="/app/docs/reference?mode=compact&amp;view=list&amp;locale=de_DE#category_motion"', $localized);
    $this->assertStringContainsString('href="https://share.catrobat.org/app/docs?locale=de_DE"', $localized);
    $this->assertStringContainsString('href="?view=full&amp;locale=de_DE#heading"', $localized);
    $this->assertSame(4, substr_count($localized, 'locale=de_DE'));
    foreach (['other?locale=fr', '#on-this-page', 'https://catrobat.org/app/docs', '//example.org/app/docs', '/app/docs-preview', '../../../project/123', '/docs-media/tutorial.png?v=abc'] as $unchanged) {
      $this->assertStringContainsString('href="'.$unchanged.'"', $localized);
    }
    $this->assertStringContainsString('src="/docs-media/tutorial.png?v=abc"', $localized);
  }

  public function testKeepsOldMediaLibraryBookmarksAlongsideTheCurrentHeadings(): void
  {
    $english = <<<'MARKDOWN'
      ### Add a new actor from the media library
      ### Add a background from the media library
      ### Add a sound from the media library
      ### Add an object from the media library
      MARKDOWN;
    $german = <<<'MARKDOWN'
      ### Füge eine neue Figur aus der Medienbibliothek hinzu
      ### Füge einen Hintergrund aus der Medienbibliothek hinzu
      ### Füge einen Sound aus der Medienbibliothek hinzu
      ### Füge ein Objekt aus der Medienbibliothek hinzu
      MARKDOWN;
    foreach ([
      ['en', $english, ['Actor', 'Background', 'Sound', 'Object']],
      ['de_DE', $german, ['Figur-Medienbibliothek', 'Hintergrund-Medienbibliothek', 'Sound-Medienbibliothek', 'Objekt-Medienbibliothek']],
    ] as [$locale, $markdown, $old_ids]) {
      $result = $this->markdown()->convert($markdown, $locale.'/getting-started/elements/medialibrary.md');
      $this->assertCount(4, $result['toc']);
      foreach ($old_ids as $index => $old_id) {
        $this->assertStringContainsString('<h3 id="'.$result['toc'][$index]['id'].'"><span id="'.$old_id.'"></span>', $result['html']);
      }
    }
  }

  public function testRendersAPageSafely(): void
  {
    $result = $this->markdown()->convert($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md', ['tutorials/jump.png' => '6b7fa434f9']);

    $this->assertSame('Your first project', $result['front_matter']['title']);
    $this->assertSame([
      ['id' => 'move-the-cat', 'title' => 'Move the cat', 'level' => 2],
      ['id' => 'watch-it', 'title' => 'Watch it', 'level' => 2],
      ['id' => 'next-steps', 'title' => 'Next steps', 'level' => 2],
    ], $result['toc']);
    $html = $result['html'];
    $this->assertStringContainsString('<h2 id="move-the-cat">', $html);
    $this->assertStringContainsString('&lt;script&gt;', $html);
    $this->assertStringNotContainsString('<script>', $html);
    $this->assertStringNotContainsString('javascript:', $html);
    // Page links relative to /{theme}/docs/getting-started/first-project; media from share's static copy.
    $this->assertStringContainsString('href="../../docs"', $html);
    $this->assertStringContainsString('href="../tutorials/jump#jump-higher"', $html);
    $this->assertStringContainsString('src="/docs-media/tutorials/jump.png?v=6b7fa434f9"', $html, 'served by share, not a third party, versioned');
  }

  public function testRendersEmbeds(): void
  {
    $html = $this->markdown()->convert($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md')['html'];

    $this->assertStringContainsString('<div class="docs-embed docs-embed--brick" data-brick-type="SetXBrick">', $html);
    $this->assertStringContainsString('<div class="docs-embed docs-embed--formula" data-formula-name="SIN">', $html);
    $this->assertStringContainsString('The formula ::formula{name=SIN} only works', $html, 'inside a paragraph an embed stays text');
    $this->assertStringContainsString('data-youtube-id="dQw4w9WgXcQ"', $html);
    $this->assertStringNotContainsString('youtube-nocookie', $html, 'nothing loads from YouTube before a click');
    $this->assertStringContainsString('<a class="docs-embed docs-embed--project" href="../../project/docs-example-project">', $html);
    $this->assertStringContainsString('<span class="docs-embed__name">Jump &amp; Run</span>', $html);
  }

  public function testProjectWithLocalFrameDoesNotLoadThePlayer(): void
  {
    $html = $this->markdown()->convert(
      "::project{id=docs-example-project}\n",
      'en/tutorials/jump.md',
      ['game-tutorials/examples/docs-example-project.webp' => '0123456789'],
    )['html'];

    $this->assertStringContainsString('src="/docs-media/game-tutorials/examples/docs-example-project.webp?v=0123456789"', $html);
    $this->assertStringContainsString('data-docs-project-id="docs-example-project"', $html);
    $this->assertStringContainsString('Play an example', $html);
    $this->assertStringNotContainsString('<iframe', $html);
  }

  public function testEmbedsMayBeIndentedLikeOtherBlocks(): void
  {
    $html = $this->markdown()->convert("  ::video{youtube=dQw4w9WgXcQ}\n\n- ::video{youtube=dQw4w9WgXcQ}\n", 'en/index.md')['html'];
    $this->assertSame(2, substr_count($html, 'data-youtube-id'), 'up to three spaces, and in a list');

    $code = $this->markdown()->convert("    ::video{youtube=dQw4w9WgXcQ}\n", 'en/index.md')['html'];
    $this->assertStringNotContainsString('data-youtube-id', $code, 'four spaces are a code block');
  }

  public function testInvalidEmbedsStayVisibleAsText(): void
  {
    $html = $this->markdown()->convert("::video{youtube=\"><script>}\n\n::unknown{x=1}\n\n::project{id=gone}\n", 'en/index.md')['html'];

    $this->assertStringNotContainsString('<script>', $html);
    $this->assertStringContainsString('<p>::unknown{x=1}</p>', $html);
    $this->assertStringContainsString('<p class="docs-embed docs-embed--project-missing"><a href="project/gone">gone</a></p>', $html);
  }

  public function testFileAndBrickLinksStayOnShare(): void
  {
    $html = $this->markdown()->convert("[Brick](../reference/bricks/SetXBrick.md) [Notes](../scripts/notes.txt)\n", 'en/index.md')['html'];

    $this->assertStringContainsString('href="docs/reference/bricks/SetXBrick"', $html);
    $this->assertStringContainsString('href="https://blob.example/scripts/notes.txt"', $html);
  }

  public function testALinkToTheReferenceFolderLeadsToTheReference(): void
  {
    $html = $this->markdown()->convert("[All](../../reference/) [Looks](../../reference/#category_looks)\n", 'de_DE/tutorials/jump.md')['html'];

    $this->assertStringContainsString('href="../reference"', $html);
    $this->assertStringContainsString('href="../reference#category_looks"', $html);
  }

  public function testABrickExplanationLinksFromItsBrickPage(): void
  {
    $html = $this->markdown()->convert("[Jump](../../en/tutorials/jump.md) [Set y](ChangeYByNBrick.md)\n", 'reference/bricks/SetXBrick.md')['html'];

    $this->assertStringContainsString('href="../../tutorials/jump"', $html);
    $this->assertStringContainsString('href="ChangeYByNBrick"', $html);
  }

  public function testEmbedsShowTheBrickAndFormulaFromTheCatalog(): void
  {
    $runner = new CodeExportRunner(self::FIXTURES.'../CodeExport', new NullLogger());
    $localizer = new CodeViewLocalizer($runner);
    $catalog = new DocsCatalog($runner, $localizer, new CodeTreeBuilder($this->createStub(CodeViewExportStore::class), $localizer, new RequestStack()), new ArrayAdapter(), __DIR__.'/../../../assets/Project/CodeViewCategories.json');
    $markdown = new DocsMarkdown('https://blob.example/', static fn (string $id): ?array => null, $catalog);

    $html = $markdown->convert($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md')['html'];
    $this->assertStringContainsString('<a class="docs-embed docs-embed--brick" href="../reference/bricks/SetXBrick" data-brick-type="SetXBrick"><div class="cv-block cv-block--motion">Set x to <span class="cv-field">100</span></div></a>', $html);
    $this->assertStringContainsString('<a class="docs-embed docs-embed--formula" href="../reference/formula/SIN" data-formula-name="SIN"><span class="cv-field">sine</span></a>', $html);

    $german = $markdown->convert("::brick{type=SetXBrick}\n\n::brick{type=NoSuchBrick}\n", 'de_DE/index.md')['html'];
    $this->assertStringContainsString('Setze x auf', $german, 'in the language of the page');
    $this->assertStringContainsString('<div class="docs-embed docs-embed--brick" data-brick-type="NoSuchBrick">', $german, 'an unknown brick shows its name');
  }

  public function testMediaLinksGoToTheStaticCopy(): void
  {
    $media = ['a.png' => '0123456789', 'teachers/sheet 1.pdf' => 'abcdef0123'];
    $markdown = '[Sheet](../../media/teachers/sheet%201.pdf) ![A](../../media/a.png) [Gone](../../media/gone.png)'
      ." [Out](../../media/../en/a.png) [Up](../../../a.png)\n";
    $html = $this->markdown()->convert($markdown, 'en/tutorials/jump.md', $media)['html'];

    $this->assertStringContainsString('href="/docs-media/teachers/sheet%201.pdf?v=abcdef0123"', $html, 'from a nested page');
    $this->assertStringContainsString('src="/docs-media/a.png?v=0123456789"', $html);
    $this->assertStringContainsString('href="../../media/gone.png"', $html, 'a missing file keeps its link');
    $this->assertStringContainsString('href="https://blob.example/en/a.png"', $html, 'no way out of media/');
    $this->assertStringContainsString('href="../../../a.png"', $html, 'no way out of the repo');
    $this->assertStringNotContainsString('/docs-media/../', $html);
  }

  public function testMarkdownHasAbsoluteLinksAndEmbedsAsLinks(): void
  {
    $runner = new CodeExportRunner(self::FIXTURES.'../CodeExport', new NullLogger());
    $localizer = new CodeViewLocalizer($runner);
    $catalog = new DocsCatalog($runner, $localizer, new CodeTreeBuilder($this->createStub(CodeViewExportStore::class), $localizer, new RequestStack()), new ArrayAdapter(), __DIR__.'/../../../assets/Project/CodeViewCategories.json');
    $markdown = new DocsMarkdown('https://blob.example/', static fn (string $id): ?array => null, $catalog, self::FIXTURES);

    $text = $markdown->markdown($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md', 'https://share.example/app/');

    $this->assertStringStartsWith('## Move the cat', $text, 'no front matter');
    $this->assertStringContainsString('[Set x to 100](https://share.example/app/docs/reference/bricks/SetXBrick)', $text);
    $this->assertStringContainsString('[sine](https://share.example/app/docs/reference/formula/SIN)', $text);
    $this->assertStringContainsString('The formula ::formula{name=SIN} only works', $text, 'an embed inside a line stays text');
    $this->assertStringContainsString('[YouTube](https://www.youtube.com/watch?v=dQw4w9WgXcQ)', $text);
    $this->assertStringContainsString('[docs-example-project](https://share.example/app/project/docs-example-project)', $text);
    $this->assertStringContainsString('[back to the start](https://share.example/app/docs)', $text);
    $this->assertStringContainsString('[how to jump](https://share.example/app/docs/tutorials/jump#jump-higher)', $text);
    $this->assertStringContainsString('](https://share.example/docs-media/tutorials/jump.png?v=6b7fa434f9)', $text, 'versioned from the file on disk');
    $this->assertStringContainsString('](https://share.example/docs-media/gone.png)', $markdown->markdown('![Gone](../media/gone.png)', 'en/index.md', 'https://share.example/app/'), 'a missing file has no version');

    // The HTML rendering afterwards is relative again.
    $html = $markdown->convert($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md')['html'];
    $this->assertStringNotContainsString('share.example', $html);
  }

  private function markdown(): DocsMarkdown
  {
    return new DocsMarkdown('https://blob.example/', static fn (string $id): ?array => 'docs-example-project' === $id
      ? ['name' => 'Jump & Run', 'author' => 'Catrobat', 'thumbnail' => '/thumb.png'] : null);
  }

  private function fixture(string $file): string
  {
    return (string) file_get_contents(self::FIXTURES.$file);
  }
}
