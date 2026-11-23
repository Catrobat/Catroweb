<?php

declare(strict_types=1);

namespace App\Application\Controller\Docs;

use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** The user docs, rendered from doc_page in the language of the visitor (or `?locale=`), English when untranslated. */
class DocsController extends AbstractController
{
  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
  ) {
  }

  #[Route(path: '/docs/{path}', name: 'docs', requirements: ['path' => '[A-Za-z0-9_/-]*'], defaults: ['path' => ''], methods: ['GET'])]
  public function page(Request $request, string $path): Response
  {
    if ('index' === $path) {
      return $this->redirectToRoute('docs', $request->query->all(), Response::HTTP_MOVED_PERMANENTLY);
    }
    $path = '' === $path ? 'index' : trim($path, '/');
    $requested = $request->query->getString('locale');
    $explicit = 1 === preg_match('/^[a-zA-Z]{2,3}([_-][a-zA-Z0-9]{2,8})?$/', $requested);
    $locale = $explicit ? $requested : $request->getLocale();

    $page = $this->library->page($path, $locale);
    if (null === $page) {
      throw $this->createNotFoundException('Docs page not found.');
    }

    $navigation = $this->library->navigation($locale, (string) $request->attributes->get('flavor'));
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
}
