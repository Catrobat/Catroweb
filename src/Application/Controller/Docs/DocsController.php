<?php

declare(strict_types=1);

namespace App\Application\Controller\Docs;

use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
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
    $link = fn (array $item): array => ['title' => $item['title'], 'url' => $this->guideUrl($item['path']), 'current' => $item['path'] === $path];
    $sections = $this->sections($navigation, $trail[0]['path'] ?? '');
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
      'docs_nav' => $this->docsNav(
        $sections,
        'index' === $path ? [] : [$this->home(), ...array_map($link, $trail)],
        array_map($link, $siblings),
        null === $parent ? null : $this->guideUrl($parent['path']),
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
    ]);
  }

  /** The brick reference: every category with its bricks, and the formula elements. */
  #[Route(path: '/docs/reference', name: 'docs_reference', methods: ['GET'], priority: 1)]
  public function reference(Request $request): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $catalog = $this->catalog->catalog($locale);
    foreach ($catalog['categories'] as &$category) {
      $category['html'] = array_map($this->catalog->html(...), $category['bricks']);
    }
    unset($category);

    return $this->render('Docs/DocsReference.html.twig', [
      'categories' => $catalog['categories'],
      'formula' => $catalog['formula'],
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference'),
        [$this->home(), ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference'), 'current' => true]],
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
    if (null === $type) {
      // A brick Catroid no longer has: the reference is the best page left (temporary, Catroid may bring it back).
      return $this->redirectToRoute('docs_reference', [], Response::HTTP_FOUND);
    }

    return $this->redirectToRoute('docs_brick', ['type' => $type], Response::HTTP_MOVED_PERMANENTLY);
  }

  #[Route(path: '/docs/reference/bricks/{type}', name: 'docs_brick', requirements: ['type' => '[A-Za-z][A-Za-z0-9_]{0,99}'], methods: ['GET'], priority: 1)]
  public function brick(Request $request, string $type): Response
  {
    [$locale, $explicit] = $this->locale($request);
    $brick = $this->catalog->brick($type, $locale);
    if (null === $brick) {
      throw $this->createNotFoundException('Unknown brick.');
    }
    $prose = $this->library->brickProse($type, $locale);
    $title = $this->catalog->title($brick['brick']);
    // Its section is its own category, the first one (DocsCatalog::brick).
    $category = $brick['categories'][0];
    $bricks = array_values(array_filter($this->catalog->catalog($locale)['categories'], static fn (array $listed): bool => $listed['key'] === $category['key']))[0]['bricks'] ?? [];
    $category_url = $this->generateUrl('docs_reference').'#'.$category['key'];

    return $this->render('Docs/DocsBrick.html.twig', [
      'type' => $type,
      'title' => $title,
      'brick_html' => $this->catalog->html($brick['brick']),
      'categories' => $brick['categories'],
      'prose' => $prose,
      'fallback' => null !== $prose && DocsLibrary::isFallback($prose, $locale),
      'edit_url' => $this->sync->brickEditUrl($type, null !== $this->library->brickProse($type, 'en')),
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference'),
        [...$this->referenceCrumbs(), ['title' => $category['name'], 'url' => $category_url, 'current' => false], ['title' => $title, 'url' => '', 'current' => true]],
        array_map(fn (array $listed): array => [
          'title' => $this->catalog->title($listed),
          'url' => $this->generateUrl('docs_brick', ['type' => $listed['type']]),
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
    $element = $this->catalog->formula($name, $locale);
    if (null === $element) {
      throw $this->createNotFoundException('Unknown formula element.');
    }

    $title = $element['label'] ?? $element['name'];
    $kind_url = $this->generateUrl('docs_reference').'#formula';

    return $this->render('Docs/DocsFormula.html.twig', [
      'element' => $element,
      'docs_nav' => $this->docsNav(
        $this->sections($this->navigation($request, $locale), 'reference'),
        [...$this->referenceCrumbs(), ['title' => $this->translator->trans('docs.reference.'.$element['kind'], [], 'catroweb'), 'url' => $kind_url, 'current' => false], ['title' => $title, 'url' => '', 'current' => true]],
        array_map(fn (array $listed): array => [
          'title' => $listed['label'] ?? $listed['name'],
          'url' => $this->generateUrl('docs_formula', ['name' => $listed['name']]),
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
   *
   * @return list<array{title: string, url: string, current: bool}>
   */
  private function sections(array $navigation, string $current): array
  {
    $sections = [];
    foreach ($navigation as $item) {
      if ('index' !== $item['path']) {
        $sections[] = ['title' => $item['title'], 'url' => $this->guideUrl($item['path']), 'current' => $item['path'] === $current];
      }
    }
    $sections[] = ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference'), 'current' => 'reference' === $current];

    return $sections;
  }

  /** @return array{title: string, url: string, current: bool} */
  private function home(): array
  {
    return ['title' => $this->translator->trans('docs.title', [], 'catroweb'), 'url' => $this->guideUrl('index'), 'current' => false];
  }

  /** @return list<array{title: string, url: string, current: bool}> */
  private function referenceCrumbs(): array
  {
    return [$this->home(), ['title' => $this->translator->trans('docs.reference.title', [], 'catroweb'), 'url' => $this->generateUrl('docs_reference'), 'current' => false]];
  }

  private function guideUrl(string $path): string
  {
    return $this->generateUrl('docs', ['path' => 'index' === $path ? '' : $path]);
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
    $canonical = $explicit ? (array_values(array_intersect($this->library->locales($locale), $supported))[0] ?? 'en') : null;

    return [
      'canonical_url' => $url($canonical),
      'alternates' => array_values(array_map(static fn (string $alternate): array => ['locale' => $alternate, 'url' => $url($alternate)], $supported)),
      'x_default_url' => $url(null),
    ];
  }
}
