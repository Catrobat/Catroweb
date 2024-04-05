<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\Api\ResponseCache;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractResponseManager.
 */
abstract class AbstractResponseManager implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  public function __construct(
    TranslatorInterface $translator,
    protected SerializerInterface $serializer,
    protected ResponseCacheManager $response_cache_manager
  ) {
    $this->initTranslator($translator);
  }

  /**
   * The Response hash is added to the header to allow clients to distinguish if the response body must be requested
   * and then loaded. For example, to update a project category shown on a landing page. If the hash between the new
   * and old requests did not change, there is no need to request/load the new response body. This workflow can lead
   * to a significant performance boost.
   */
  public function addResponseHashToHeaders(array &$responseHeaders, mixed $response): void
  {
    $responseHeaders['X-Response-Hash'] = md5($this->getSerializer()->serialize($response, 'application/json'));
  }

  public function addContentLanguageToHeaders(array &$responseHeaders): void
  {
    $responseHeaders['Content-Language'] = $this->getLocale();
  }

  public function getCachedResponse(string $cache_id, string $time = '-180 minutes'): ?ResponseCache
  {
    /** @var ResponseCache|null $cache_entry */
    $cache_entry = $this->response_cache_manager->getResponseCacheRepository()->findOneBy(['id' => $cache_id]);

    if ('prod' === $_ENV['APP_ENV'] && null !== $cache_entry && $cache_entry->getCachedAt() > new \DateTime($time)) {
      return $cache_entry;
    }

    return null;
  }

  public function cacheResponse(string $cache_id, int $response_code, array $responseHeaders, mixed $response): void
  {
    $this->response_cache_manager->addCacheEntry($cache_id, $response_code, $responseHeaders, $response);
  }

  protected function getSerializer(): SerializerInterface
  {
    return $this->serializer;
  }

  public function extractResponseObject(ResponseCache $cache_entry): array
  {
    return unserialize($cache_entry->getResponse()) ?? [];
  }

  public function extractResponseHeader(ResponseCache $cache_entry): array
  {
    return json_decode($cache_entry->getResponseHeaders(), true, 512, JSON_THROW_ON_ERROR) ?? [];
  }
}
