<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use App\Docs\DocsSync;
use App\Docs\Search\DocsSearch;
use App\Docs\Search\DocsSearchIndexer;
use OpenAPI\Server\Api\DocsApiInterface;
use OpenAPI\Server\Model\CodeViewBrick;
use OpenAPI\Server\Model\DocsBrickResponse;
use OpenAPI\Server\Model\DocsCatalogResponse;
use OpenAPI\Server\Model\DocsFormulaCatalog;
use OpenAPI\Server\Model\DocsNavigationResponse;
use OpenAPI\Server\Model\DocsPageResponse;
use OpenAPI\Server\Model\DocsSearchResponse;
use OpenAPI\Server\Model\DocsSearchResult;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;

class DocsApi implements DocsApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly DocsLibrary $library,
    private readonly DocsSync $sync,
    private readonly RequestStack $requests,
    private readonly DocsCatalog $catalog,
    private readonly DocsSearch $search,
    private readonly RateLimiterFactory $docsSearchBurstLimiter,
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

  #[\Override]
  public function docsReferenceCatalogGet(?string $locale, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = self::cacheControl($locale);
    $locale = $this->locale($locale);
    $catalog = $this->catalog->catalog($locale);

    return new DocsCatalogResponse([
      'locale' => $locale,
      'categories' => $catalog['categories'],
      'formula' => new DocsFormulaCatalog($catalog['formula']),
    ]);
  }

  #[\Override]
  public function docsReferenceBricksTypeGet(string $type, ?string $locale, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $cache_control = self::cacheControl($locale);
    $locale = $this->locale($locale);
    $brick = $this->catalog->brick($type, $locale);
    if (null === $brick) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return ApiErrorResponse::createModel($responseCode, 'not_found', 'Brick not found.');
    }
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = $cache_control;
    $prose = $this->library->brickProse($type, $locale);

    return new DocsBrickResponse([
      'type' => $type,
      'locale' => $locale,
      'title' => $this->catalog->title($brick['brick']),
      'brick' => new CodeViewBrick($brick['brick']),
      'categories' => $brick['categories'],
      'html' => $prose?->getHtml(),
      'html_locale' => $prose?->getLocale(),
      'fallback' => null !== $prose && DocsLibrary::isFallback($prose, $locale),
      'translation_state' => $prose?->getTranslationState(),
      'reviewed' => $prose?->isReviewed() ?? false,
      'source_url' => $this->sync->brickEditUrl($type, null !== $this->library->brickProse($type, 'en')),
    ]);
  }

  /** The search page asks this next to every /api/search call: docs_search_burst, per client IP. */
  #[\Override]
  public function docsSearchGet(string $q, ?string $locale, int $limit, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $rate_limit = $this->checkIpRateLimit($this->requests->getCurrentRequest()?->getClientIp() ?? 'unknown', $this->docsSearchBurstLimiter);
    if (null === $rate_limit) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }
    $length = mb_strlen(trim($q));
    if (0 === $length || $length > DocsSearch::MAX_QUERY_LENGTH) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return ApiErrorResponse::createModel($responseCode, 'invalid_query', 'q must have 1 to '.DocsSearch::MAX_QUERY_LENGTH.' characters.');
    }
    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = self::cacheControl($locale);
    $locale = $this->locale($locale);
    $result = $this->search->search($q, $locale, $limit);
    $group = static fn (string $type): array => array_map(
      static fn (array $hit): DocsSearchResult => new DocsSearchResult($hit),
      $result['groups'][$type] ?? [],
    );

    return new DocsSearchResponse([
      'locale' => $locale,
      'semantic' => $result['semantic'],
      'guides' => $group(DocsSearchIndexer::GUIDE),
      'bricks' => $group(DocsSearchIndexer::BRICK),
      'formula' => $group(DocsSearchIndexer::FORMULA),
    ]);
  }
}
