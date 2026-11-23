<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use OpenAPI\Server\Api\DocsApiInterface;
use OpenAPI\Server\Model\DocsNavigationResponse;
use OpenAPI\Server\Model\DocsPageResponse;
use Symfony\Component\HttpFoundation\Response;

class DocsApi implements DocsApiInterface
{
  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
  ) {
  }

  #[\Override]
  public function docsGet(?string $locale, ?string $flavor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = 'public, max-age=300';

    return new DocsNavigationResponse(['locale' => $locale ?? 'en', 'items' => $this->library->navigation($locale ?? 'en', $flavor)]);
  }

  #[\Override]
  public function docsPagesPathGet(string $path, ?string $locale, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $locale ??= 'en';
    $page = $this->library->page($path, $locale);
    if (null === $page) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return ApiErrorResponse::createModel($responseCode, 'not_found', 'Page not found.');
    }
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = 'public, max-age=300';

    return new DocsPageResponse([
      'path' => $page->getPath(),
      'locale' => $page->getLocale(),
      'fallback' => DocsLibrary::isFallback($page, $locale),
      'title' => $page->getTitle(),
      'description' => $page->getDescription(),
      'html' => $page->getHtml(),
      'toc' => $page->getToc(),
      'translation_state' => $page->getTranslationState(),
      'reviewed' => $page->isReviewed(),
      'flavors' => $page->getFlavors(),
      'source_url' => $this->sync->editUrl($page),
      'updated_at' => $page->getUpdatedAt(),
    ]);
  }
}
