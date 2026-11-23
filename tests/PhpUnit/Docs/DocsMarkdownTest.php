<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\Docs\DocsMarkdown;
use App\Docs\Markdown\EmbedParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
      'folder index' => ['de/tutorials/index.md', ['kind' => 'guide', 'locale' => 'de', 'path' => 'tutorials']],
      'page' => ['pt-BR/tutorials/jump.md', ['kind' => 'guide', 'locale' => 'pt-BR', 'path' => 'tutorials/jump']],
      'brick' => ['reference/bricks/SetXBrick.md', ['kind' => 'brick', 'locale' => 'en', 'path' => 'SetXBrick']],
      'translated brick' => ['de/reference/bricks/SetXBrick.md', ['kind' => 'brick', 'locale' => 'de', 'path' => 'SetXBrick']],
      'readme' => ['README.md', null],
      'media' => ['media/tutorials/jump.png', null],
      'scripts' => ['scripts/notes.md', null],
      'traversal' => ['en/../secret.md', null],
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

  public function testRendersAPageSafely(): void
  {
    $result = $this->markdown()->convert($this->fixture('en/getting-started/first-project.md'), 'en/getting-started/first-project.md');

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
    // Page links relative to /{theme}/docs/getting-started/first-project; media from GitHub.
    $this->assertStringContainsString('href="../../docs"', $html);
    $this->assertStringContainsString('href="../tutorials/jump#jump-higher"', $html);
    $this->assertStringContainsString('src="https://raw.example/media/tutorials/jump.png"', $html);
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

  public function testInvalidEmbedsStayVisibleAsText(): void
  {
    $html = $this->markdown()->convert("::video{youtube=\"><script>}\n\n::unknown{x=1}\n\n::project{id=gone}\n", 'en/index.md')['html'];

    $this->assertStringNotContainsString('<script>', $html);
    $this->assertStringContainsString('<p>::unknown{x=1}</p>', $html);
    $this->assertStringContainsString('<p class="docs-embed docs-embed--project-missing"><a href="project/gone">gone</a></p>', $html);
  }

  public function testFileLinksGoToGitHub(): void
  {
    $html = $this->markdown()->convert("[Worksheet](../media/teachers/sheet%201.pdf) [Brick](../reference/bricks/SetXBrick.md)\n", 'en/index.md')['html'];

    $this->assertStringContainsString('href="https://blob.example/media/teachers/sheet%201.pdf"', $html);
    $this->assertStringContainsString('href="https://blob.example/reference/bricks/SetXBrick.md"', $html);
  }

  private function markdown(): DocsMarkdown
  {
    return new DocsMarkdown('https://raw.example/', 'https://blob.example/', static fn (string $id): ?array => 'docs-example-project' === $id
      ? ['name' => 'Jump & Run', 'author' => 'Catrobat', 'thumbnail' => '/thumb.png'] : null);
  }

  private function fixture(string $file): string
  {
    return (string) file_get_contents(self::FIXTURES.$file);
  }
}
