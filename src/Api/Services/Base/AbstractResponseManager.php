<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use OpenAPI\Server\Service\SerializerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractResponseManager.
 */
abstract class AbstractResponseManager implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  private const int DEFAULT_CACHE_TTL = 10800; // 3 hours in seconds

  public function __construct(
    TranslatorInterface $translator,
    protected SerializerInterface $serializer,
    protected CacheItemPoolInterface $cache,
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

  /**
   * Get cached response data.
   *
   * @return array{response_code: int, response_headers: array, response: mixed}|null
   *
   * @throws InvalidArgumentException
   */
  public function getCachedResponse(string $cache_id): ?array
  {
    if ('prod' !== ($_ENV['APP_ENV'] ?? 'dev')) {
      return null;
    }

    $item = $this->cache->getItem($this->sanitizeCacheKey($cache_id));

    return $item->isHit() ? $item->get() : null;
  }

  /**
   * Cache response data.
   *
   * @throws InvalidArgumentException
   */
  public function cacheResponse(string $cache_id, int $response_code, array $responseHeaders, mixed $response, int $ttl = self::DEFAULT_CACHE_TTL): void
  {
    if ('prod' !== ($_ENV['APP_ENV'] ?? 'dev')) {
      return;
    }

    $item = $this->cache->getItem($this->sanitizeCacheKey($cache_id));
    $item->set([
      'response_code' => $response_code,
      'response_headers' => $responseHeaders,
      'response' => $response,
    ]);
    $item->expiresAfter($ttl);
    $this->cache->save($item);
  }

  /**
   * Get cached response or compute it.
   *
   * @param callable(CacheItemInterface): array{response_code: int, response_headers: array, response: mixed} $callback
   *
   * @return array{response_code: int, response_headers: array, response: mixed}
   *
   * @throws InvalidArgumentException
   */
  public function getCachedOrCompute(string $cache_id, callable $callback, int $ttl = self::DEFAULT_CACHE_TTL): array
  {
    if ('prod' !== ($_ENV['APP_ENV'] ?? 'dev')) {
      // In non-prod environments, always compute without caching
      return $callback(new class implements CacheItemInterface, ItemInterface {
        public function getKey(): string
        {
          return '';
        }

        public function get(): mixed
        {
          return null;
        }

        public function isHit(): bool
        {
          return false;
        }

        public function set(mixed $value): static
        {
          return $this;
        }

        public function expiresAt(?\DateTimeInterface $expiration): static
        {
          return $this;
        }

        public function expiresAfter(\DateInterval|int|null $time): static
        {
          return $this;
        }

        public function getMetadata(): array
        {
          return [];
        }

        public function tag(string|iterable $tags): static
        {
          return $this;
        }
      });
    }

    $cache_key = $this->sanitizeCacheKey($cache_id);
    $item = $this->cache->getItem($cache_key);

    if ($item->isHit()) {
      return $item->get();
    }

    $result = $callback($item);
    $item->set($result);
    $item->expiresAfter($ttl);
    $this->cache->save($item);

    return $result;
  }

  /**
   * Invalidate cache entry.
   *
   * @throws InvalidArgumentException
   */
  public function invalidateCache(string $cache_id): void
  {
    $this->cache->deleteItem($this->sanitizeCacheKey($cache_id));
  }

  protected function getSerializer(): SerializerInterface
  {
    return $this->serializer;
  }

  /**
   * Sanitize cache key to be PSR-6 compliant.
   */
  private function sanitizeCacheKey(string $key): string
  {
    // PSR-6 forbids: {}()/\@:
    return str_replace(['/', '\\', '@', ':', '{', '}', '(', ')'], '_', $key);
  }
}
