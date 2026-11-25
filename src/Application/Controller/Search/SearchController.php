<?php

declare(strict_types=1);

namespace App\Application\Controller\Search;

use App\Docs\DocsLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
  #[Route(path: '/search/', name: 'empty_search', defaults: ['q' => null], methods: ['GET'], priority: 1)]
  #[Route(path: '/search/{q}', name: 'search', requirements: ['q' => '.+'], methods: ['GET'])]
  public function search(Request $request, DocsLibrary $library, ?string $q = null): Response
  {
    $docs_only = 'docs' === $request->query->getString('context') && 'docs' === $request->query->getString('scope');
    $q ??= $docs_only ? trim($request->query->getString('q')) : null;
    $locale = $request->query->getString('locale');
    $explicit_locale = $docs_only && 1 === preg_match('/^[a-zA-Z]{2,3}([_-][a-zA-Z0-9]{2,8})?$/', $locale) ? $locale : null;
    $locale = $explicit_locale ?? $request->getLocale();

    return $this->render('Search/SearchPage.html.twig', [
      'q' => $q,
      'docs_only' => $docs_only,
      'docs_locale' => $locale,
      'docs_ui_locale' => $library->shareLocale($locale),
      'docs_explicit_locale' => $explicit_locale,
      'docs_sections' => $docs_only ? array_column($library->navigation($locale), 'title', 'path') : [],
    ]);
  }
}
