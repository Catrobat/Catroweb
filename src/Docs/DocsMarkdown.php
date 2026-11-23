<?php

declare(strict_types=1);

namespace App\Docs;

use App\DB\Entity\Docs\DocPage;
use App\Docs\Markdown\Embed;
use App\Docs\Markdown\EmbedParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Node\StringContainerHelper;
use League\CommonMark\Normalizer\SlugNormalizer;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\HtmlRenderer;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Renders one file of the docs repo: front matter, Markdown (raw HTML escaped, unsafe links dropped), the embeds,
 * heading ids and the table of contents.
 *
 * Links are rewritten so the HTML works on every theme without knowing it: a link to another page becomes a URL
 * relative to the page's own URL (`/{theme}/docs/<path>`), a file in media/ points at share's synced copy
 * (`/{theme}/docs/_media/<path>`, so no page view reaches a third party), any other repo file at its GitHub page.
 */
final class DocsMarkdown implements NodeRendererInterface
{
  /** Bump when the HTML for the same Markdown changes: the next sync renders every page again. */
  public const int VERSION = 1;

  private readonly MarkdownParser $parser;

  private readonly HtmlRenderer $renderer;

  private readonly FrontMatterParser $front_matter;

  /** URL of the page being rendered, relative to the theme root (`docs/tutorials/jump`). */
  private string $page_url = 'docs';

  /**
   * @param string                                                                    $blob_base    GitHub's page of a file at the synced ref, with trailing slash
   * @param \Closure(string): ?array{name: string, author: string, thumbnail: string} $project_card a public project, or null
   */
  public function __construct(
    private readonly string $blob_base,
    private readonly \Closure $project_card,
  ) {
    $environment = new Environment(['html_input' => 'escape', 'allow_unsafe_links' => false]);
    $environment->addExtension(new CommonMarkCoreExtension());
    $environment->addExtension(new GithubFlavoredMarkdownExtension());
    $environment->addBlockStartParser(new EmbedParser(), 100);
    $environment->addRenderer(Embed::class, $this);
    $this->parser = new MarkdownParser($environment);
    $this->renderer = new HtmlRenderer($environment);
    $this->front_matter = new FrontMatterParser(new SymfonyYamlFrontMatterParser());
  }

  /**
   * Where a repo file goes: `en/tutorials/jump.md` is the guide `tutorials/jump` in `en`, `de/index.md` the guide
   * `index` in `de`, `reference/bricks/SetXBrick.md` the explanation of SetXBrick in `en`. Null for anything else.
   *
   * @return array{kind: string, locale: string, path: string}|null
   */
  public static function locate(string $file): ?array
  {
    if (1 === preg_match('#^(?:([a-z]{2,3}(?:[_-][A-Za-z0-9]{2,8})?)/)?reference/bricks/(\w+)\.md$#', $file, $m)) {
      return ['kind' => DocPage::BRICK, 'locale' => '' === $m[1] ? 'en' : $m[1], 'path' => $m[2]];
    }
    if (1 !== preg_match('#^([a-z]{2,3}(?:[_-][A-Za-z0-9]{2,8})?)/((?:[A-Za-z0-9][A-Za-z0-9_-]*/)*[A-Za-z0-9][A-Za-z0-9_-]*)\.md$#', $file, $m)) {
      return null;
    }
    $path = (string) preg_replace('#(^|/)index$#', '', $m[2]);

    return ['kind' => DocPage::GUIDE, 'locale' => $m[1], 'path' => '' === $path ? 'index' : $path];
  }

  /** The page's URL relative to the theme root: `docs` for the root index, `docs/<path>` for the rest. */
  public static function pageUrl(string $path): string
  {
    return 'index' === $path ? 'docs' : 'docs/'.$path;
  }

  /**
   * @return array{front_matter: array<array-key, mixed>, html: string, toc: list<array{id: string, title: string, level: int}>}
   */
  public function convert(string $markdown, string $file): array
  {
    $input = $this->front_matter->parse($markdown);
    $front_matter = $input->getFrontMatter();
    $location = self::locate($file);
    $this->page_url = null !== $location && DocPage::GUIDE === $location['kind'] ? self::pageUrl($location['path']) : 'docs';

    $document = $this->parser->parse($input->getContent());
    $toc = [];
    $slugs = new SlugNormalizer();
    $used = [];
    foreach ($document->iterator() as $node) {
      if ($node instanceof Heading) {
        $title = StringContainerHelper::getChildText($node, [RawMarkupContainerInterface::class]);
        $id = $slugs->normalize($title);
        $id = '' === $id ? 'section' : $id;
        $used[$id] = ($used[$id] ?? 0) + 1;
        $id = 1 === $used[$id] ? $id : $id.'-'.($used[$id] - 1);
        $node->data->set('attributes/id', $id);
        if (\in_array($node->getLevel(), [2, 3], true)) {
          $toc[] = ['id' => $id, 'title' => $title, 'level' => $node->getLevel()];
        }
      } elseif ($node instanceof Link || $node instanceof Image) {
        $node->setUrl($this->rewriteUrl($node->getUrl(), $file));
      }
    }

    return [
      'front_matter' => \is_array($front_matter) ? $front_matter : [],
      'html' => trim($this->renderer->renderDocument($document)->getContent()),
      'toc' => $toc,
    ];
  }

  #[\Override]
  public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
  {
    \assert($node instanceof Embed);
    $value = htmlspecialchars($node->value, ENT_QUOTES);

    return match ($node->kind) {
      // M2 replaces these with the brick and formula element as Catroid renders them; until then the name shows.
      'brick' => '<div class="docs-embed docs-embed--brick" data-brick-type="'.$value.'"><span class="material-icons" aria-hidden="true">extension</span><code>'.$value.'</code></div>',
      'formula' => '<div class="docs-embed docs-embed--formula" data-formula-name="'.$value.'"><span class="material-icons" aria-hidden="true">functions</span><code>'.$value.'</code></div>',
      // Nothing loads from YouTube until the reader clicks (docs Stimulus controller); without JS it is a plain link.
      'video' => '<div class="docs-embed docs-embed--video"><a class="docs-video" href="https://www.youtube.com/watch?v='.$value.'" data-youtube-id="'.$value.'" target="_blank" rel="noopener noreferrer"><span class="material-icons" aria-hidden="true">play_circle</span><span class="docs-video__label">YouTube</span></a></div>',
      default => $this->renderProject($node->value),
    };
  }

  private function renderProject(string $id): string
  {
    $card = ($this->project_card)($id);
    $href = htmlspecialchars(self::relativeUrl($this->page_url, 'project/'.$id), ENT_QUOTES);
    if (null === $card) {
      return '<p class="docs-embed docs-embed--project-missing"><a href="'.$href.'">'.htmlspecialchars($id, ENT_QUOTES).'</a></p>';
    }

    return '<a class="docs-embed docs-embed--project" href="'.$href.'">'
      .'<img class="docs-embed__image" src="'.htmlspecialchars($card['thumbnail'], ENT_QUOTES).'" alt="" width="80" height="80" loading="lazy">'
      .'<span class="docs-embed__text"><span class="docs-embed__name">'.htmlspecialchars($card['name'], ENT_QUOTES).'</span>'
      .'<span class="docs-embed__author">'.htmlspecialchars($card['author'], ENT_QUOTES).'</span></span></a>';
  }

  private function rewriteUrl(string $url, string $file): string
  {
    if ('' === $url || 1 === preg_match('#^([a-z][a-z0-9+.-]*:|/|\#|\?)#i', $url)) {
      return $url; // external, site-absolute or an anchor on the page
    }
    $parts = explode('#', $url, 2);
    $target = $parts[0];
    $fragment = $parts[1] ?? null;
    $resolved = self::resolve(\dirname($file), $target);
    if (null === $resolved) {
      return $url;
    }
    $location = self::locate($resolved);
    if (null !== $location && DocPage::GUIDE === $location['kind']) {
      return self::relativeUrl($this->page_url, self::pageUrl($location['path'])).(null === $fragment ? '' : '#'.$fragment);
    }
    $encoded = implode('/', array_map(rawurlencode(...), explode('/', $resolved)));

    return null === DocsSync::mediaPath($resolved) ? $this->blob_base.$encoded : self::relativeUrl($this->page_url, 'docs/_'.$encoded);
  }

  /** `en/tutorials` + `../media/a.png` = `media/a.png`; null when it leaves the repo. */
  private static function resolve(string $dir, string $target): ?string
  {
    $parts = '.' === $dir ? [] : explode('/', $dir);
    foreach (explode('/', rawurldecode($target)) as $segment) {
      if ('..' === $segment) {
        if ([] === $parts) {
          return null;
        }
        array_pop($parts);
      } elseif ('' !== $segment && '.' !== $segment) {
        $parts[] = $segment;
      }
    }

    return [] === $parts ? null : implode('/', $parts);
  }

  /** A URL that leads from the page at $from to $to, both relative to the theme root. */
  public static function relativeUrl(string $from, string $to): string
  {
    $from_dir = explode('/', $from);
    array_pop($from_dir);
    $to_parts = explode('/', $to);
    while ([] !== $from_dir && \count($to_parts) > 1 && $from_dir[0] === $to_parts[0]) {
      array_shift($from_dir);
      array_shift($to_parts);
    }

    return str_repeat('../', \count($from_dir)).implode('/', $to_parts);
  }
}
