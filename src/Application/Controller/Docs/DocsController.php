<?php

declare(strict_types=1);

namespace App\Application\Controller\Docs;

use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use App\Docs\DocsMarkdown;
use App\Docs\DocsSync;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** The user docs, rendered from doc_page in the language of the visitor (or `?locale=`), English when untranslated. */
class DocsController extends AbstractController
{
  /** The longest "In this section" list: a longer section shows the nearest pages and links to all of them. */
  public const int MAX_SIBLINGS = 15;

  /**
   * The brick section imported from catrobat.org (bricks/<category>) is folded into the reference: its pages lead to
   * the reference's category. Its own name for a category, where the reference's key is not category_<name>.
   */
  private const array OLD_BRICK_CATEGORIES = ['yourbricks' => 'user_bricks'];

  /** Common groups in learning order. Catroid-only extensions follow in the catalog's order. */
  private const array COMMON_CATEGORIES = [
    'category_event' => 'start',
    'category_motion' => 'move',
    'category_looks' => 'looks',
    'category_sound' => 'sound',
    'category_control' => null,
    'category_pen' => null,
    'category_data' => null,
    'category_user_bricks' => null,
    'category_device' => null,
  ];

  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
    private readonly DocsCatalog $catalog,
    private readonly TranslatorInterface $translator,
  ) {
  }

  #[Route(path: '/docs/{path}', name: 'docs', requirements: ['path' => '[A-Za-z0-9_/-]*'], defaults: ['path' => ''], methods: ['GET'])]
  public function page(Request $request, string $path): Response
  {
    if ('index' === $path) {
      // Only the language survives: other query values (?theme=) must not reach the route.
      [$locale, $explicit] = $this->locale($request);

      return $this->redirectToRoute('docs', $explicit ? ['locale' => $locale] : [], Response::HTTP_MOVED_PERMANENTLY);
    }
    $path = '' === $path ? 'index' : trim($path, '/');
    [$locale, $explicit] = $this->locale($request);
    $query = $explicit ? ['locale' => $locale] : [];
    // Also with the language in the path, as the import's redirects name a translation (de_DE/bricks/looks).
    if (1 === preg_match('#^(?:([a-z]{2,3}(?:_[A-Za-z0-9]{2,8})?)/)?bricks(?:/([a-z]+))?(?:/|$)#', $path, $m)) {
      return $this->oldBrickPage($m[2] ?? '', $explicit ? $locale : ('' === ($m[1] ?? '') ? null : $m[1]));
    }

    $page = $this->library->page($path, $locale);
    if (null === $page) {
      throw $this->createNotFoundException('Docs page not found.');
    }

    $navigation = $this->navigation($request, $locale);
    $flat = DocsLibrary::flatten($navigation);
    $position = array_search($path, array_column($flat, 'path'), true);
    $trail = DocsLibrary::trail($navigation, $path);
    $parent = $trail[\count($trail) - 2] ?? null;
    $link = fn (array $item): array => ['title' => $item['title'], 'url' => $this->guideUrl($item['path'], $query), 'current' => $item['path'] === $path];
    $sections = $this->sections($navigation, $trail[0]['path'] ?? '', $query);
    /** @var list<array{path: string, title: string, children: array<mixed>}> $children */
    $children = ([] === $trail ? [] : $trail[\count($trail) - 1]['children']);
    /** @var list<array{path: string, title: string, children: array<mixed>}> $siblings */
    $siblings = $parent['children'] ?? [];
    $url = fn (string $page_path, ?string $page_locale = null): string => $this->generateUrl(
      'docs',
      ['theme' => 'app', 'path' => 'index' === $page_path ? '' : $page_path] + (null === $page_locale || 'en' === $page_locale ? [] : ['locale' => $page_locale]),
      UrlGeneratorInterface::ABSOLUTE_URL,
    );

    return $this->render('Docs/DocsPage.html.twig', [
      'page' => $page,
      'body_html' => DocsMarkdown::withLinkLocale($page->getHtml(), $request->getUri(), $this->generateUrl('docs'), $query['locale'] ?? null),
      'docs_locale_query' => $query,
      'docs_ui_locale' => $this->library->shareLocale($locale),
      'docs_nav' => $this->docsNav(
        $sections,
        'index' === $path ? [] : [$this->home($query), ...array_map($link, $trail)],
        array_map($link, $siblings),
        null === $parent ? null : $this->guideUrl($parent['path'], $query),
        // The docs home lists the sections; on a phone the app sidebar that also lists them is closed.
        'index' === $path ? $sections : array_map($link, $children),
      ),
      'previous' => false === $position ? null : ($flat[$position - 1] ?? null),
      'next' => false === $position ? null : ($flat[$position + 1] ?? null),
      'fallback' => DocsLibrary::isFallback($page, $locale),
      'edit_url' => $this->sync->editUrl($page),
      // One indexed copy per language: every theme points at /app/docs, a translation at its ?locale= URL.
      'canonical_url' => $url($path, $explicit ? $page->getLocale() : null),
      'alternates' => array_map(static fn (string $alternate): array => ['locale' => $alternate, 'url' => $url($path, $alternate)], $this->library->translations($path)),
      'x_default_url' => $url($path),
      'markdown_url' => $this->generateUrl('docs_markdown', ['path' => $path] + $query),
    ]);
  }

  /** A guide as Markdown, for readers that want text (docs/operations/User-Docs.md, "Markdown and llms.txt"). */
  #[Route(path: '/docs/{path}.md', name: 'docs_markdown', requirements: ['path' => '[A-Za-z0-9_/-]+'], methods: ['GET'], priority: 1)]
  public function pageMarkdown(Request $request, string $path): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $page = $this->library->page($path, $locale);
    $body = null === $page ? null : $this->sync->markdown($page, $this->themeRoot());
    if (null === $page || null === $body) {
      throw $this->createNotFoundException('Docs page not found.');
    }
    $description = $page->getDescription();

    return $this->markdownResponse('# '.$page->getTitle()."\n\n".(null === $description ? '' : '> '.$description."\n\n").$body, $explicit);
  }

  /** A brick's reference page as Markdown: its text, categories and explanation. */
  #[Route(path: '/docs/reference/bricks/{type}.md', name: 'docs_brick_markdown', requirements: ['type' => '[A-Za-z][A-Za-z0-9_]{0,99}'], methods: ['GET'], priority: 2)]
  public function brickMarkdown(Request $request, string $type): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $brick = $this->catalog->brick($type, $locale);
    if (null === $brick) {
      throw $this->createNotFoundException('Unknown brick.');
    }
    $prose = $this->library->brickProse($type, $locale);
    $body = null === $prose ? null : $this->sync->markdown($prose, $this->themeRoot());

    return $this->markdownResponse(
      '# '.$this->catalog->title($brick['brick'])."\n\n"
      .'Catrobat brick `'.$type.'`, in '.implode(', ', array_column($brick['categories'], 'name')).".\n"
      .(null === $brick['setting'] ? '' : "\n> ".$this->settingHint($brick['setting'], $locale)."\n")
      .(null === $body ? '' : "\n".$body),
      $explicit,
    );
  }

  /**
   * llms.txt (llmstxt.org) at the site root (config/routes/routes.php): the English docs as links to their Markdown,
   * section by section, then every brick.
   */
  public function llmsTxt(): Response
  {
    $root = $this->themeRoot();
    $home = $this->library->page('index', 'en');
    $lines = [
      '# '.($home?->getTitle() ?? 'Catrobat documentation'),
      '',
      '> '.($home?->getDescription() ?? 'Learn to make games, animations and apps with Pocket Code.'),
      '',
      'User documentation of Pocket Code and the other Catrobat apps. Every page is Markdown at the linked URL; without `.md` it is the web page. Add `?locale=<language>` (de, es, ...) for a translation.',
      '',
      '- [Catrobat documentation]('.$root.'docs/index.md)',
    ];
    foreach ($this->library->navigation('en') as $section) {
      if ('index' === $section['path']) {
        continue;
      }
      $lines[] = '';
      $lines[] = '## '.$section['title'];
      $lines[] = '';
      foreach (DocsLibrary::flatten([$section]) as $item) {
        $lines[] = '- ['.$item['title'].']('.$root.'docs/'.$item['path'].'.md)';
      }
    }
    $lines[] = '';
    $lines[] = '## '.$this->translator->trans('docs.reference.title', [], 'catroweb', 'en');
    $lines[] = '';
    $listed = [];
    foreach ($this->catalog->catalog('en')['categories'] as $category) {
      foreach ($category['bricks'] as $brick) {
        $type = (string) $brick['type'];
        if (!isset($listed[$type])) {
          $listed[$type] = true;
          $lines[] = '- ['.$this->catalog->title($brick).']('.$root.'docs/reference/bricks/'.$type.'.md): '.$category['name'];
        }
      }
    }

    return $this->markdownResponse(implode("\n", $lines), true, 'text/plain; charset=utf-8');
  }

  /** The brick reference: every category with its bricks, and the formula elements. */
  #[Route(path: '/docs/reference', name: 'docs_reference', methods: ['GET'], priority: 1)]
  public function reference(Request $request): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $query = $explicit ? ['locale' => $locale] : [];
    $catalog = $this->catalog->catalog($locale);
    $categories = $catalog['categories'];
    $order = array_flip(array_keys(self::COMMON_CATEGORIES));
    usort($categories, static fn (array $a, array $b): int => ($order[$a['key']] ?? PHP_INT_MAX) <=> ($order[$b['key']] ?? PHP_INT_MAX));
    foreach ($categories as &$category) {
      $category['common'] = \array_key_exists($category['key'], self::COMMON_CATEGORIES);
      $category['jump_label'] = self::COMMON_CATEGORIES[$category['key']] ?? null;
      $category['html'] = array_map($this->catalog->html(...), $category['bricks']);
    }
    unset($category);

    return $this->render('Docs/DocsReference.html.twig', [
      'categories' => $categories,
      'formula' => $catalog['formula'],
      'docs_locale_query' => $query,
      'docs_ui_locale' => $this->library->shareLocale($locale),
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference', $query),
        [$this->home($query), ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference', $query), 'current' => true]],
      ),
    ] + $this->alternates('docs_reference', [], $locale, $explicit));
  }

  /** An old brick section page: the reference, at the category when it was a category page (`#category_looks`). */
  private function oldBrickPage(string $category, ?string $locale): Response
  {
    $url = $this->generateUrl('docs_reference', null === $locale ? [] : ['locale' => $locale]);

    return $this->redirect('' === $category ? $url : $url.'#category_'.(self::OLD_BRICK_CATEGORIES[$category] ?? $category), Response::HTTP_MOVED_PERMANENTLY);
  }

  /**
   * Old links named a brick by its Catroid class (wiki.catrobat.org/bin/view/Documentation/BrickDocumentation/<Class>,
   * the app's help before it used share): a redirect rule sends them here (docs/operations/User-Docs.md).
   */
  #[Route(path: '/docs/reference/bricks/by-class/{class}', name: 'docs_brick_by_class', requirements: ['class' => '[A-Za-z][A-Za-z0-9_]{0,99}'], methods: ['GET'], priority: 1)]
  public function brickByClass(Request $request, string $class): Response
  {
    $type = $this->catalog->typeOfClass($class);
    // The app's help asks in its language (?locale=): the page it is sent to keeps it.
    [$locale, $explicit] = $this->locale($request);
    $query = $explicit ? ['locale' => $locale] : [];
    if (null === $type) {
      // A brick Catroid no longer has: the reference is the best page left (temporary, Catroid may bring it back).
      return $this->redirectToRoute('docs_reference', $query, Response::HTTP_FOUND);
    }

    return $this->redirectToRoute('docs_brick', ['type' => $type] + $query, Response::HTTP_MOVED_PERMANENTLY);
  }

  #[Route(path: '/docs/reference/bricks/{type}', name: 'docs_brick', requirements: ['type' => '[A-Za-z][A-Za-z0-9_]{0,99}'], methods: ['GET'], priority: 1)]
  public function brick(Request $request, string $type): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $query = $explicit ? ['locale' => $locale] : [];
    $brick = $this->catalog->brick($type, $locale);
    if (null === $brick) {
      throw $this->createNotFoundException('Unknown brick.');
    }
    $prose = $this->library->brickProse($type, $locale);
    $title = $this->catalog->title($brick['brick']);
    // A brick can be opened from several groups. Use the group the reader followed when it contains this brick.
    $memberships = array_column($brick['categories'], null, 'key');
    $category = $memberships[$request->query->getString('category')] ?? $brick['categories'][0];
    $bricks = array_values(array_filter($this->catalog->catalog($locale)['categories'], static fn (array $listed): bool => $listed['key'] === $category['key']))[0]['bricks'] ?? [];
    $category_url = $this->generateUrl('docs_reference', $query).'#'.$category['key'];

    return $this->render('Docs/DocsBrick.html.twig', [
      'type' => $type,
      'title' => $title,
      'brick_html' => $this->catalog->html($brick['brick']),
      'categories' => [$category['key'] => $category] + $memberships,
      'setting_hint' => null === $brick['setting'] ? null : $this->settingHint($brick['setting'], $locale),
      'prose' => $prose,
      'body_html' => null === $prose ? '' : DocsMarkdown::withLinkLocale($prose->getHtml(), $request->getUri(), $this->generateUrl('docs'), $query['locale'] ?? null),
      'fallback' => null !== $prose && DocsLibrary::isFallback($prose, $locale),
      'edit_url' => $this->sync->brickEditUrl($type, null !== $this->library->brickProse($type, 'en')),
      'markdown_url' => $this->generateUrl('docs_brick_markdown', ['type' => $type] + $query),
      'docs_locale_query' => $query,
      'docs_ui_locale' => $this->library->shareLocale($locale),
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference', $query),
        [...$this->referenceCrumbs($query), ['title' => $category['name'], 'url' => $category_url, 'current' => false], ['title' => $title, 'url' => '', 'current' => true]],
        array_map(fn (array $listed): array => [
          'title' => $this->catalog->title($listed),
          'url' => $this->generateUrl('docs_brick', ['type' => $listed['type']] + $query + ['category' => $category['key']]),
          'current' => $listed['type'] === $type,
        ], $bricks),
        $category_url,
      ),
    ] + $this->alternates('docs_brick', ['type' => $type], $locale, $explicit));
  }

  #[Route(path: '/docs/reference/formula/{name}', name: 'docs_formula', requirements: ['name' => '[A-Za-z][A-Za-z0-9_]{0,99}'], methods: ['GET'], priority: 1)]
  public function formula(Request $request, string $name): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $query = $explicit ? ['locale' => $locale] : [];
    $element = $this->catalog->formula($name, $locale);
    if (null === $element) {
      throw $this->createNotFoundException('Unknown formula element.');
    }

    $title = $element['label'] ?? $element['name'];
    $kind_url = $this->generateUrl('docs_reference', $query).'#formula';

    return $this->render('Docs/DocsFormula.html.twig', [
      'element' => $element,
      'docs_locale_query' => $query,
      'docs_ui_locale' => $this->library->shareLocale($locale),
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference', $query),
        [...$this->referenceCrumbs($query), ['title' => $this->translator->trans('docs.reference.'.$element['kind'], [], 'catroweb'), 'url' => $kind_url, 'current' => false], ['title' => $title, 'url' => '', 'current' => true]],
        array_map(fn (array $listed): array => [
          'title' => $listed['label'] ?? $listed['name'],
          'url' => $this->generateUrl('docs_formula', ['name' => $listed['name']] + $query),
          'current' => $listed['name'] === $name,
        ], $this->catalog->catalog($locale)['formula'][$element['kind']] ?? []),
        $kind_url,
      ),
    ] + $this->alternates('docs_formula', ['name' => $name], $locale, $explicit));
  }

  /**
   * The docs navigation (docs/operations/User-Docs.md): the sections for the app sidebar, the breadcrumb, the pages
   * beside the page in its section and the pages below it. Of a long section only the nearest MAX_SIBLINGS show, with
   * a link to all of them.
   *
   * @param list<array{title: string, url: string, current: bool}> $sections
   * @param list<array{title: string, url: string, current: bool}> $crumbs
   * @param list<array{title: string, url: string, current: bool}> $siblings
   * @param list<array{title: string, url: string, current: bool}> $pages
   *
   * @return array{sections: list<array{title: string, url: string, current: bool}>, crumbs: list<array{title: string, url: string, current: bool}>, siblings: list<array{title: string, url: string, current: bool}>, all_url: string|null, pages: list<array{title: string, url: string, current: bool}>}
   */
  private function docsNav(array $sections, array $crumbs, array $siblings = [], ?string $all_url = null, array $pages = []): array
  {
    $current = array_search(true, array_column($siblings, 'current'), true);

    return [
      'sections' => $sections,
      'crumbs' => $crumbs,
      'siblings' => DocsLibrary::window($siblings, false === $current ? 0 : $current, self::MAX_SIBLINGS),
      'all_url' => \count($siblings) > self::MAX_SIBLINGS ? $all_url : null,
      'pages' => $pages,
    ];
  }

  /**
   * The top-level guides except the docs home, then the brick reference.
   *
   * @param list<array{path: string, title: string, children: array<mixed>}> $navigation
   * @param array{locale?: string}                                           $query
   *
   * @return list<array{title: string, url: string, current: bool}>
   */
  private function sections(array $navigation, string $current, array $query): array
  {
    $sections = [];
    foreach ($navigation as $item) {
      if ('index' !== $item['path']) {
        $sections[] = ['title' => $item['title'], 'url' => $this->guideUrl($item['path'], $query), 'current' => $item['path'] === $current];
      }
    }
    $sections[] = ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference', $query), 'current' => 'reference' === $current];

    return $sections;
  }

  /**
   * @param array{locale?: string} $query
   *
   * @return array{title: string, url: string, current: bool}
   */
  private function home(array $query): array
  {
    return ['title' => $this->translator->trans('docs.title', [], 'catroweb'), 'url' => $this->guideUrl('index', $query), 'current' => false];
  }

  /**
   * @param array{locale?: string} $query
   *
   * @return list<array{title: string, url: string, current: bool}>
   */
  private function referenceCrumbs(array $query): array
  {
    return [$this->home($query), ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference', $query), 'current' => false]];
  }

  /** The absolute URL of the canonical theme root (`https://share.catrobat.org/app/`), for links in Markdown. */
  private function themeRoot(): string
  {
    return $this->generateUrl('index', ['theme' => 'app'], UrlGeneratorInterface::ABSOLUTE_URL);
  }

  /** Like the docs API: a CDN may only share a response that depends on the URL alone, not on Accept-Language. */
  private function markdownResponse(string $text, bool $shared, string $type = 'text/markdown; charset=utf-8'): Response
  {
    return new Response($text."\n", Response::HTTP_OK, [
      'Content-Type' => $type,
      'Cache-Control' => ($shared ? 'public' : 'private').', max-age=300',
      // The source may contain raw HTML: never let a browser run it.
      'X-Content-Type-Options' => 'nosniff',
    ]);
  }

  /** @param array{locale?: string} $query */
  private function guideUrl(string $path, array $query): string
  {
    return $this->generateUrl('docs', ['path' => 'index' === $path ? '' : $path] + $query);
  }

  /** "To see this brick, turn on …": in the page's language, which ?locale= may set apart from the request's. */
  private function settingHint(string $setting, string $locale): string
  {
    return $this->translator->trans('docs.reference.setting', ['%setting%' => $setting], 'catroweb', $this->library->shareLocale($locale));
  }

  /**
   * The language of the view and whether the URL chose it: `?locale=` when it is one, else the visitor's.
   *
   * @return array{string, bool}
   */
  private function locale(Request $request): array
  {
    $requested = $request->query->getString('locale');
    $explicit = 1 === preg_match('/^[a-zA-Z]{2,3}([_-][a-zA-Z0-9]{2,8})?$/', $requested);

    return [$explicit ? $requested : $request->getLocale(), $explicit];
  }

  /**
   * @return list<array{path: string, title: string, children: array<mixed>}>
   */
  private function navigation(Request $request, string $locale): array
  {
    return $this->library->navigation($locale, (string) $request->attributes->get('flavor'));
  }

  /**
   * Canonical and hreflang URLs of a reference page, which exists in every language share speaks (Catroid's own
   * translations): one indexed copy per language on /app/docs, a language other than English at its ?locale= URL.
   *
   * @param array<string, string> $params
   *
   * @return array{canonical_url: string, alternates: list<array{locale: string, url: string}>, x_default_url: string}
   */
  private function alternates(string $route, array $params, string $locale, bool $explicit): array
  {
    $url = fn (?string $page_locale): string => $this->generateUrl(
      $route,
      ['theme' => 'app'] + $params + (null === $page_locale || 'en' === $page_locale ? [] : ['locale' => $page_locale]),
      UrlGeneratorInterface::ABSOLUTE_URL,
    );

    $supported = $this->library->getSupportedLanguageCodes();
    // ?locale=de and ?locale=de_DE show the same page: the canonical one is share's own locale.
    $canonical = $explicit ? $this->library->shareLocale($locale) : null;

    return [
      'canonical_url' => $url($canonical),
      'alternates' => array_values(array_map(static fn (string $alternate): array => ['locale' => $alternate, 'url' => $url($alternate)], $supported)),
      'x_default_url' => $url(null),
    ];
  }
}
