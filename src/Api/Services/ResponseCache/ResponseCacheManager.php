<?php

declare(strict_types=1);

namespace App\Api\Services\ResponseCache;

use App\DB\Entity\Api\ResponseCache;
use Doctrine\ORM\EntityManagerInterface;

class ResponseCacheManager
{
  public function __construct(protected EntityManagerInterface $entity_manager, protected ResponseCacheRepository $response_cache_repository)
  {
  }

  /**
   * @throws \JsonException
   */
  public function addCacheEntry(string $cache_id, int $response_code, array $response_headers, mixed $response): ResponseCache
  {
    /** @var ResponseCache|null $cache_entry */
    $cache_entry = $this->getResponseCacheRepository()->findOneBy(['id' => $cache_id]);
    if (null !== $cache_entry) {
      $cache_entry
        ->setResponseCode($response_code)
        ->setResponse(serialize($response))
        ->setResponseHeaders(json_encode($response_headers, JSON_THROW_ON_ERROR))
        ->updateTimestamps()
      ;
    } else {
      $cache_entry = (new ResponseCache())
        ->setId($cache_id)
        ->setResponseCode($response_code)
        ->setResponse(serialize($response))
        ->setResponseHeaders(json_encode($response_headers, JSON_THROW_ON_ERROR))
        ->updateTimestamps()
      ;
    }

    $this->entity_manager->persist($cache_entry);
    $this->entity_manager->flush();

    return $cache_entry;
  }

  public function deleteCacheEntry(ResponseCache $cache_entry): void
  {
    $this->entity_manager->remove($cache_entry);
    $this->entity_manager->flush();
  }

  public function getResponseCacheRepository(): ResponseCacheRepository
  {
    return $this->response_cache_repository;
  }
}
