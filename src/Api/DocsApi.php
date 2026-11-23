<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use OpenAPI\Server\Api\DocsApiInterface;
use OpenAPI\Server\Model\DocsNavigationResponse;
use OpenAPI\Server\Model\DocsPageResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DocsApi implements DocsApiInterface
{
  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
    private readonly RequestStack $requests,
  ) {
  }

  /** Without `locale`, the client's language (Accept-Language, as everywhere in the API). */
  private function locale(?string $locale): string
  {
    return $locale ?? $this->requests->getCurrentRequest()?->getLocale() ?? 'en';
  }

  /** A CDN may only share a response that depends on the URL alone, not on Accept-Language or the hl cookie. */
  private static function cacheControl(?string $locale): string
  {
    return null === $locale ? 'private, max-age=300' : 'public, max-age=300';
  }

  #[\Override]
  public function docsGet(?string $locale, ?string $flavor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = self::cacheControl($locale);
    $locale = $this->locale($locale);

    return new DocsNavigationResponse(['locale' => $locale, 'items' => $this->library->navigation($locale, $flavor)]);
  }

  #[\Override]
  public function docsPagesPathGet(string $path, ?string $locale, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $cache_control = self::cacheControl($locale);
    $locale = $this->locale($locale);
    $page = $this->library->page($path, $locale);
    if (null === $page) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return ApiErrorResponse::createModel($responseCode, 'not_found', 'Page not found.');
    }
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = $cache_control;

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
