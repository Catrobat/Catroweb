<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\EntityRepository\Project\ProjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Searches Scratch's public project search live, so users can find any Scratch project and convert it.
 * Best effort: when Scratch is slow, down or the client is rate limited, the result is simply empty.
 */
class ScratchSearch
{
  private const int CACHE_TTL = 600;
  // Scratch rejects larger pages with HTTP 400.
  private const int MAX_LIMIT = 40;
  private const int MAX_BYTES = 1024 * 1024;
  // Every save since Scratch 3 launched is a Scratch 3 project; older ones are Scratch 2 or 1.x and can't be converted.
  private const string SCRATCH_3_RELEASE = '2019-01-02';

  public function __construct(
    private readonly HttpClientInterface $http_client,
    private readonly CacheInterface $cache,
    private readonly RateLimiterFactoryInterface $scratchSearchBurstLimiter,
    private readonly ProjectRepository $project_repository,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Scratch reports no total: it is offset + the size of Scratch's page, plus one while that page is full, so "show more"
   * stays offered. Counted before imported and pre-Scratch-3 projects are left out, which would otherwise shorten a full page.
   *
   * @return array{projects: list<array{id: string, name: string, username: string}>, total: int}
   */
  public function search(string $query, int $limit, int $offset, string $ip): array
  {
    $query = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $query)));
    $limit = min($limit, self::MAX_LIMIT);
    if ('' === $query || $limit < 1 || $offset < 0) {
      return ['projects' => [], 'total' => 0];
    }

    $page = $this->cache->get(
      'scratch_search_'.md5($query.'|'.$offset.'|'.$limit),
      function (ItemInterface $item, bool &$save) use ($query, $limit, $offset, $ip): array {
        $item->expiresAfter(self::CACHE_TTL);
        // Only cache misses ask Scratch, so only they count against the limit.
        $page = $this->scratchSearchBurstLimiter->create($ip)->consume()->isAccepted()
          ? $this->fetch($query, $limit, $offset)
          : null;
        $save = null !== $page;

        return $page ?? [];
      }
    );

    // Imported projects are real Share projects and show up as such; checked after the cache, as imports happen anytime.
    $imported = $this->project_repository->findImportedScratchIds(array_map(static fn (array $project): int => (int) $project['id'], $page));
    $projects = [];
    foreach ($page as $project) {
      if (($project['modified'] ?? self::SCRATCH_3_RELEASE) >= self::SCRATCH_3_RELEASE && !\in_array((int) $project['id'], $imported, true)) {
        $projects[] = ['id' => $project['id'], 'name' => $project['name'], 'username' => $project['username']];
      }
    }

    return [
      'projects' => $projects,
      'total' => $offset + \count($page) + (\count($page) === $limit ? 1 : 0),
    ];
  }

  /**
   * modified is the ISO time of the project's last save on Scratch.
   *
   * @return list<array{id: string, name: string, username: string, modified?: string}>|null null when Scratch could not answer
   */
  protected function fetch(string $query, int $limit, int $offset): ?array
  {
    try {
      $response = $this->http_client->request('GET', 'https://api.scratch.mit.edu/search/projects', [
        'query' => ['q' => $query, 'mode' => 'popular', 'language' => 'en', 'limit' => $limit, 'offset' => $offset],
        // Runs inside every default search, so a slow Scratch may delay it by at most 2 s.
        'timeout' => 2,
        'max_duration' => 2,
        'max_redirects' => 0,
        'headers' => ['User-Agent' => 'Catroweb Scratch search (+https://share.catrobat.org)'],
        'on_progress' => static function (int $downloaded): void {
          if ($downloaded > self::MAX_BYTES) {
            throw new \RuntimeException('Scratch search response too large');
          }
        },
      ]);
      $data = $response->toArray();
    } catch (\Throwable $throwable) {
      $this->logger->warning('Scratch search failed', ['query' => $query, 'error' => $throwable->getMessage()]);

      return null;
    }

    $projects = [];
    foreach ($data as $project) {
      $id = $project['id'] ?? null;
      $name = $project['title'] ?? null;
      $username = $project['author']['username'] ?? null;
      $modified = $project['history']['modified'] ?? null;
      if (\is_int($id) && \is_string($name) && \is_string($username)) {
        $projects[] = ['id' => (string) $id, 'name' => $name, 'username' => $username] + (\is_string($modified) ? ['modified' => $modified] : []);
      }
    }

    return $projects;
  }
}
