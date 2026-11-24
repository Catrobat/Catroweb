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
 * Links are rewritten so the HTML works on every theme without knowing it: a link to another page or to a brick's
 * explanation becomes a URL relative to the page's own URL (`/{theme}/docs/<path>`,
 * `/{theme}/docs/reference/bricks/<type>`), a link to the reference/ folder leads to the reference (`/{theme}/docs/reference`,
 * with its anchor: `#category_looks`), a file in media/ points at share's static copy
 * (`/docs-media/<path>`, so no page view reaches a third party), any other repo file at its GitHub page.
 *
 * `::brick` and `::formula` show the brick or formula element as the brick reference does (DocsCatalog), in the
 * page's language, linked to its reference page.
 */
final class DocsMarkdown implements NodeRendererInterface
{
  /** Bump when the HTML for the same Markdown changes: the next sync renders every page again. */
  public const int VERSION = 4;

  private readonly MarkdownParser $parser;

  private readonly HtmlRenderer $renderer;

  private readonly FrontMatterParser $front_matter;

  /** URL of the page being rendered, relative to the theme root (`docs/tutorials/jump`). */
  private string $page_url = 'docs';

  /** Language of the page being rendered, for the bricks it embeds. */
  private string $locale = 'en';

  /** @var array<string, int> media of the sync: path inside media/ => index */
  private array $media = [];

  /** While markdown() runs, the absolute URL of the theme root (`https://share.catrobat.org/app/`): links are absolute. */
  private ?string $root = null;

  /**
   * @param string                                                                    $blob_base    GitHub's page of a file at the synced ref, with trailing slash
   * @param \Closure(string): ?array{name: string, author: string, thumbnail: string} $project_card a public project, or null
   */
  public function __construct(
    private readonly string $blob_base,
    private readonly \Closure $project_card,
    private readonly ?DocsCatalog $catalog = null,
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

  /** A brick's reference page relative to the theme root. */
  public static function brickUrl(string $type): string
  {
    return 'docs/reference/bricks/'.$type;
  }

  /**
   * @param list<string> $media paths inside media/ (DocsSync::readMedia)
   *
   * @return array{front_matter: array<array-key, mixed>, html: string, toc: list<array{id: string, title: string, level: int}>}
   */
  public function convert(string $markdown, string $file, array $media = []): array
  {
    $this->media = array_flip($media);
    $input = $this->front_matter->parse($markdown);
    $front_matter = $input->getFrontMatter();
    $location = self::locate($file);
    $this->page_url = null === $location ? 'docs' : match ($location['kind']) {
      DocPage::GUIDE => self::pageUrl($location['path']),
      DocPage::BRICK => self::brickUrl($location['path']),
      default => 'docs',
    };
    $this->locale = $location['locale'] ?? 'en';

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

  /**
   * A file as Markdown for readers that want text (the `.md` URLs, docs/operations/User-Docs.md): without its front
   * matter, links and images absolute, embeds as links. ponytail: links are found by a regex, so a link in a code block
   * is rewritten too; parse with the AST when the docs show Markdown source.
   *
   * @param string $root absolute URL of the theme root, with trailing slash
   */
  public function markdown(string $markdown, string $file, string $root): string
  {
    $origin = (string) preg_replace('#^([a-z]+://[^/]+).*$#i', '$1', $root);
    $this->root = $root;
    $this->locale = self::locate($file)['locale'] ?? 'en';
    try {
      $text = (string) preg_replace_callback('/(\]\(\s*<?)([^)\s>]+)/', function (array $m) use ($file, $origin): string {
        $url = $this->rewriteUrl($m[2], $file);

        return $m[1].(1 === preg_match('#^/(?!/)#', $url) ? $origin.$url : $url);
      }, $this->front_matter->parse($markdown)->getContent());

      return trim((string) preg_replace_callback('/^[ \t]*::(\w+)\{([^}]*)\}[ \t]*$/m', fn (array $m): string => $this->embedLink($m[1], $m[2]) ?? $m[0], $text));
    } finally {
      $this->root = null;
    }
  }

  #[\Override]
  public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
  {
    \assert($node instanceof Embed);
    $value = htmlspecialchars($node->value, ENT_QUOTES);

    return match ($node->kind) {
      'brick' => $this->renderBrick($node->value),
      'formula' => $this->renderFormula($node->value),
      // Nothing loads from YouTube until the reader clicks (docs Stimulus controller); without JS it is a plain link.
      'video' => '<div class="docs-embed docs-embed--video"><a class="docs-video" href="https://www.youtube.com/watch?v='.$value.'" data-youtube-id="'.$value.'" target="_blank" rel="noopener noreferrer"><span class="material-icons" aria-hidden="true">play_circle</span><span class="docs-video__label">YouTube</span></a></div>',
      default => $this->renderProject($node->value),
    };
  }

  /** The brick as the reference shows it; a type the catalog does not know shows its name (the docs repo's CI flags it). */
  private function renderBrick(string $type): string
  {
    $value = htmlspecialchars($type, ENT_QUOTES);
    $catalog = $this->catalog;
    $brick = null === $catalog ? null : $catalog->brick($type, $this->locale);
    if (null === $catalog || null === $brick) {
      return '<div class="docs-embed docs-embed--brick" data-brick-type="'.$value.'"><span class="material-icons" aria-hidden="true">extension</span><code>'.$value.'</code></div>';
    }
    $href = htmlspecialchars(self::relativeUrl($this->page_url, self::brickUrl($type)), ENT_QUOTES);

    return '<a class="docs-embed docs-embed--brick" href="'.$href.'" data-brick-type="'.$value.'">'.$catalog->html($brick['brick']).'</a>';
  }

  private function renderFormula(string $name): string
  {
    $value = htmlspecialchars($name, ENT_QUOTES);
    $element = $this->catalog?->formula($name, $this->locale);
    if (null === $element) {
      return '<div class="docs-embed docs-embed--formula" data-formula-name="'.$value.'"><span class="material-icons" aria-hidden="true">functions</span><code>'.$value.'</code></div>';
    }
    $href = htmlspecialchars(self::relativeUrl($this->page_url, 'docs/reference/formula/'.$name), ENT_QUOTES);

    return '<a class="docs-embed docs-embed--formula" href="'.$href.'" data-formula-name="'.$value.'"><span class="cv-field">'
      .htmlspecialchars($element['label'] ?? $name, ENT_QUOTES).'</span></a>';
  }

  /** An embed line as a Markdown link (markdown()), null when EmbedParser would not take it either. */
  private function embedLink(string $kind, string $arguments): ?string
  {
    $spec = EmbedParser::KINDS[$kind] ?? null;
    if (null === $spec || 1 !== preg_match('/(?:^|\s)'.$spec[0].'=(\S+)/', $arguments, $arg) || 1 !== preg_match($spec[1], $arg[1])) {
      return null;
    }
    $value = $arg[1];
    $title = $value;
    $catalog = $this->catalog;
    if ('brick' === $kind && null !== $catalog) {
      $brick = $catalog->brick($value, $this->locale);
      $title = null === $brick ? $value : $catalog->title($brick['brick']);
    }

    return match ($kind) {
      'brick' => '['.$title.']('.$this->root.self::brickUrl($value).')',
      'formula' => '['.($this->catalog?->formula($value, $this->locale)['label'] ?? $value).']('.$this->root.'docs/reference/formula/'.$value.')',
      'video' => '[YouTube](https://www.youtube.com/watch?v='.$value.')',
      default => '['.(($this->project_card)($value)['name'] ?? $value).']('.$this->root.'project/'.$value.')',
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
    // The reference folder is the reference: [Bricks](../reference/#category_looks).
    if (1 === preg_match('#^(?:[a-z]{2,3}(?:[_-][A-Za-z0-9]{2,8})?/)?reference$#', $resolved)) {
      return $this->link('docs/reference').(null === $fragment ? '' : '#'.$fragment);
    }
    $location = self::locate($resolved);
    if (null !== $location) {
      $to = DocPage::GUIDE === $location['kind'] ? self::pageUrl($location['path']) : self::brickUrl($location['path']);

      return $this->link($to).(null === $fragment ? '' : '#'.$fragment);
    }
    $encoded = implode('/', array_map(rawurlencode(...), explode('/', $resolved)));
    if (!str_starts_with($resolved, 'media/')) {
      return $this->blob_base.$encoded;
    }

    // A missing media file keeps its link; user-docs/check.sh reports it. markdown() does not know the media.
    return null !== $this->root || isset($this->media[substr($resolved, 6)]) ? '/docs-'.$encoded : $url;
  }

  /** A link to a page relative to the theme root: absolute in markdown(), else relative to the rendered page. */
  private function link(string $to): string
  {
    return null === $this->root ? self::relativeUrl($this->page_url, $to) : $this->root.$to;
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
