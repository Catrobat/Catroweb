<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\DB\EntityRepository\Project\ProjectRepository;
use App\Project\Scratch\ScratchSearch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(ScratchSearch::class)]
class ScratchSearchTest extends TestCase
{
  /** @var string[] */
  private array $requested_urls = [];

  public function testMapsScratchResultsAndCachesPerNormalizedQuery(): void
  {
    $search = $this->createSearch([new MockResponse(json_encode([
      ['id' => 101, 'title' => 'Pong', 'author' => ['username' => 'cat']],
      ['id' => 'broken'],
    ], JSON_THROW_ON_ERROR))]);

    $expected = ['projects' => [['id' => '101', 'name' => 'Pong', 'username' => 'cat']], 'total' => 1];
    $this->assertSame($expected, $search->search('  Pong ', 20, 0, '1.2.3.4'));
    // Same normalized query: served from the cache, Scratch is asked once.
    $this->assertSame($expected, $search->search('pong', 20, 0, '1.2.3.4'));
    $this->assertCount(1, $this->requested_urls);
    $this->assertStringContainsString('q=pong', $this->requested_urls[0]);
    $this->assertStringContainsString('limit=20', $this->requested_urls[0]);
  }

  public function testFullPageReportsOneMoreAndImportedProjectsAreLeftOut(): void
  {
    $search = $this->createSearch([new MockResponse(json_encode([
      ['id' => 1, 'title' => 'A', 'author' => ['username' => 'x']],
      ['id' => 2, 'title' => 'B', 'author' => ['username' => 'y']],
    ], JSON_THROW_ON_ERROR))], imported: [2]);

    $result = $search->search('a', 2, 4, '1.2.3.4');

    $this->assertSame(['1'], array_column($result['projects'], 'id'));
    $this->assertSame(4 + 2 + 1, $result['total']); // the full page counts, so leaving one out keeps "show more"
  }

  public function testProjectsNotSavedSinceScratch3AreLeftOut(): void
  {
    $search = $this->createSearch([new MockResponse(json_encode([
      ['id' => 1, 'title' => 'Nyan', 'author' => ['username' => 'x'], 'history' => ['modified' => '2018-01-09T22:34:11.000Z']],
      ['id' => 2, 'title' => 'New', 'author' => ['username' => 'y'], 'history' => ['modified' => '2019-01-02T10:00:00.000Z']],
    ], JSON_THROW_ON_ERROR))]);

    $result = $search->search('a', 2, 0, '1.2.3.4');

    $this->assertSame([['id' => '2', 'name' => 'New', 'username' => 'y']], $result['projects']);
    $this->assertSame(2 + 1, $result['total']);
  }

  public function testScratchFailureGivesEmptyResultAndIsNotCached(): void
  {
    $search = $this->createSearch([
      new MockResponse('down', ['http_code' => 503]),
      new MockResponse('[{"id": 7, "title": "Back", "author": {"username": "u"}}]'),
    ]);

    $this->assertSame(['projects' => [], 'total' => 0], $search->search('x', 20, 0, '1.2.3.4'));
    $retried = $search->search('X ', 20, 0, '1.2.3.4');
    $this->assertSame(['7'], array_column($retried['projects'], 'id'));
  }

  public function testRateLimitedClientGetsEmptyResultWithoutAskingScratch(): void
  {
    $search = $this->createSearch([new MockResponse('[]'), new MockResponse('[]')], limit: 1);

    $search->search('first', 20, 0, '1.2.3.4');
    $this->assertSame(['projects' => [], 'total' => 0], $search->search('second', 20, 0, '1.2.3.4'));
    $this->assertCount(1, $this->requested_urls);
  }

  /**
   * @param MockResponse[] $responses
   * @param int[]          $imported
   */
  private function createSearch(array $responses, array $imported = [], int $limit = 30): ScratchSearch
  {
    $http_client = new MockHttpClient(function (string $method, string $url) use (&$responses): MockResponse {
      $this->requested_urls[] = $url;

      return array_shift($responses) ?? new MockResponse('', ['http_code' => 500]);
    });
    $limiter = new RateLimiterFactory(['id' => 'scratch_search', 'policy' => 'sliding_window', 'limit' => $limit, 'interval' => '1 minute'], new InMemoryStorage());
    $project_repository = $this->createStub(ProjectRepository::class);
    $project_repository->method('findImportedScratchIds')->willReturn($imported);

    return new ScratchSearch($http_client, new ArrayAdapter(), $limiter, $project_repository, new NullLogger());
  }
}
