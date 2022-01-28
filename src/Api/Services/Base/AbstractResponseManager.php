<?php

namespace App\Api\Services\Base;

use App\Entity\ResponseCache;
use App\Manager\ResponseCacheManager;
use DateTime;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractResponseManager.
 */
abstract class AbstractResponseManager implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  protected SerializerInterface $serializer;
  protected ResponseCacheManager $response_cache_manager;

  public function __construct(
      TranslatorInterface $translator,
      SerializerInterface $serializer,
      ResponseCacheManager $response_cache_manager
  ) {
    $this->initTranslator($translator);
    $this->serializer = $serializer;
    $this->response_cache_manager = $response_cache_manager;
  }

  /**
   * The Response hash is added to the header to allow clients to distinguish if the response body must be requested
   * and then loaded. For example, to update a project category shown on a landing page. If the hash between the new
   * and old requests did not change, there is no need to request/load the new response body. This workflow can lead
   * to a significant performance boost.
   *
   * @param mixed $response
   */
  public function addResponseHashToHeaders(array &$responseHeaders, $response): void
  {
    $responseHeaders['X-Response-Hash'] = md5($this->getSerializer()->serialize($response, 'application/json'));
  }

  public function addContentLanguageToHeaders(array &$responseHeaders): void
  {
    $responseHeaders['Content-Language'] = $this->getLocale();
  }

  public function getCachedResponse($cache_id, string $time = '-10 minutes'): ?ResponseCache
  {
    /** @var ResponseCache|null $cache_entry */
    $cache_entry = $this->response_cache_manager->getResponseCacheRepository()->findOneBy(['id' => $cache_id]);

    if ('prod' === $_ENV['APP_ENV'] && null !== $cache_entry && $cache_entry->getCachedAt() > new DateTime($time)) {
      return $cache_entry;
    }

    return null;
  }

  /**
   * @param mixed $response
   */
  public function cacheResponse(string $cache_id, int $response_code, array $responseHeaders, $response): void
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
    return json_decode($cache_entry->getResponseHeaders(), true) ?? [];
  }
}
