<?php

declare(strict_types=1);

namespace App\Docs\Search;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Text embeddings for the docs search, from a text-embeddings-inference server with multilingual-e5-small
 * (DOCS_EMBEDDING_URL, docs/operations/User-Docs.md). Without a URL, or when the server fails or is slow, it returns
 * null and the search uses keywords only.
 */
class DocsEmbedder
{
  /** multilingual-e5-small's vector size; the app_doc mapping uses it. */
  final public const int DIMS = 384;

  /** A query waits this long at most: a slow model must not slow the search down. */
  private const float QUERY_TIMEOUT = 1.5;

  private const float INDEX_TIMEOUT = 60.0;

  public function __construct(
    private readonly HttpClientInterface $http_client,
    private readonly CacheInterface $cache,
    private readonly LoggerInterface $logger,
    #[Autowire(env: 'DOCS_EMBEDDING_URL')]
    private readonly string $url,
    // The model the server runs (part of the stored vectors' key, so a new model embeds everything again) and the
    // prefixes it wants: e5 models are trained with "query: " and "passage: " (multilingual-e5 model card).
    #[Autowire(env: 'DOCS_EMBEDDING_MODEL')]
    private readonly string $model = 'intfloat/multilingual-e5-small',
    #[Autowire(env: 'DOCS_EMBEDDING_QUERY_PREFIX')]
    private readonly string $query_prefix = 'query: ',
    #[Autowire(env: 'DOCS_EMBEDDING_PASSAGE_PREFIX')]
    private readonly string $passage_prefix = 'passage: ',
  ) {
  }

  /** Identifies a passage's vector: the same text, prefix and model give the same vector. */
  public function passageKey(string $passage): string
  {
    return hash('xxh128', $this->model."\n".$this->passage_prefix.$passage);
  }

  public function isEnabled(): bool
  {
    return '' !== $this->url;
  }

  /**
   * A search query's vector, cached for a while (people repeat searches, and the search box asks while typing).
   *
   * @return list<float>|null
   */
  public function embedQuery(string $query): ?array
  {
    if (!$this->isEnabled()) {
      return null;
    }
    $query = trim($query); // not lowercased: the model reads case ("Ton" is a sound, "ton" is not)

    // A failure is cached as [] for a shorter time, so a down server is not asked again on every keystroke.
    $vector = $this->cache->get('docs_query_embedding.'.hash('xxh128', $this->model."\n".$query), function (ItemInterface $item) use ($query): array {
      $vectors = $this->embed([$this->query_prefix.$query], self::QUERY_TIMEOUT);
      $item->expiresAfter(null === $vectors ? 30 : 3600);

      return $vectors[0] ?? [];
    });

    return [] === $vector ? null : $vector;
  }

  /**
   * Vectors of docs texts, in order, or null when the server is not configured or failed.
   *
   * @param list<string> $passages
   *
   * @return list<list<float>>|null
   */
  public function embedPassages(array $passages): ?array
  {
    if (!$this->isEnabled() || [] === $passages) {
      return null;
    }

    return $this->embed(array_map(fn (string $text): string => $this->passage_prefix.$text, $passages), self::INDEX_TIMEOUT);
  }

  /**
   * @param list<string> $inputs
   *
   * @return list<list<float>>|null
   */
  protected function embed(array $inputs, float $timeout): ?array
  {
    try {
      $response = $this->http_client->request('POST', rtrim($this->url, '/').'/embed', [
        'json' => ['inputs' => $inputs, 'normalize' => true, 'truncate' => true],
        'timeout' => $timeout,
        'max_duration' => $timeout,
      ]);
      $vectors = $response->toArray();
    } catch (ExceptionInterface $e) {
      $this->logger->warning('Docs embedding failed: '.$e->getMessage());

      return null;
    }
    if (\count($vectors) !== \count($inputs) || !array_is_list($vectors)) {
      $this->logger->warning('Docs embedding: unexpected response');

      return null;
    }
    foreach ($vectors as $vector) {
      if (!\is_array($vector) || self::DIMS !== \count($vector)) {
        $this->logger->warning('Docs embedding: vectors must have '.self::DIMS.' dimensions');

        return null;
      }
    }

    return array_map(static fn (array $vector): array => array_map(floatval(...), array_values($vector)), $vectors);
  }
}
