<?php

declare(strict_types=1);

namespace App\Application\Controller\Docs;

use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** The user docs, rendered from doc_page in the language of the visitor (or `?locale=`), English when untranslated. */
class DocsController extends AbstractController
{
  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
    private readonly DocsCatalog $catalog,
  ) {
  }

  /**
   * Images and PDFs from user-docs/media/ of this release, so a page view never reaches a third party. Only the
   * types in DocsSync::MEDIA_TYPES are served. SVG is served as an image but sandboxed, so opening one directly
   * cannot run script on share's origin.
   */
  // `_media`: a guide segment never starts with `_` (DocsMarkdown::locate), so no page can be shadowed.
  #[Route(path: '/docs/_media/{path}', name: 'docs_media', requirements: ['path' => '.+'], methods: ['GET'], priority: 1)]
  public function media(string $path): Response
  {
    $file = $this->sync->mediaFile($path);
    if (null === $file) {
      throw $this->createNotFoundException('Docs media not found.');
    }
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $response = new BinaryFileResponse($file, Response::HTTP_OK, ['Content-Type' => DocsSync::MEDIA_TYPES[$extension]], true, ResponseHeaderBag::DISPOSITION_INLINE, false, true);
    $response->setPublic();
    $response->setMaxAge(86400);
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    if ('svg' === $extension) {
      $response->headers->set('Content-Security-Policy', "sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:");
    }

    return $response;
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

    $page = $this->library->page($path, $locale);
    if (null === $page) {
      throw $this->createNotFoundException('Docs page not found.');
    }

    $navigation = $this->navigation($request, $locale);
    $flat = DocsLibrary::flatten($navigation);
    $position = array_search($path, array_column($flat, 'path'), true);
    $url = fn (string $page_path, ?string $page_locale = null): string => $this->generateUrl(
      'docs',
      ['theme' => 'app', 'path' => 'index' === $page_path ? '' : $page_path] + (null === $page_locale || 'en' === $page_locale ? [] : ['locale' => $page_locale]),
      UrlGeneratorInterface::ABSOLUTE_URL,
    );

    return $this->render('Docs/DocsPage.html.twig', [
      'page' => $page,
      'navigation' => $navigation,
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
      'navigation' => $this->navigation($request, $locale),
    ] + $this->alternates('docs_reference', [], $locale, $explicit));
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

    return $this->render('Docs/DocsBrick.html.twig', [
      'type' => $type,
      'title' => $this->catalog->title($brick['brick']),
      'brick_html' => $this->catalog->html($brick['brick']),
      'categories' => $brick['categories'],
      'prose' => $prose,
      'fallback' => null !== $prose && DocsLibrary::isFallback($prose, $locale),
      'edit_url' => $this->sync->brickEditUrl($type, null !== $this->library->brickProse($type, 'en')),
      'navigation' => $this->navigation($request, $locale),
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

    return $this->render('Docs/DocsFormula.html.twig', [
      'element' => $element,
      'navigation' => $this->navigation($request, $locale),
    ] + $this->alternates('docs_formula', ['name' => $name], $locale, $explicit));
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
