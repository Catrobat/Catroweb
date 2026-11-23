<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsLibrary;
use App\Docs\Search\DocsEmbedder;
use App\Docs\Search\DocsSearch;
use Elastica\Result;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\IndexManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
#[CoversClass(DocsSearch::class)]
#[CoversClass(DocsEmbedder::class)]
final class DocsSearchTest extends TestCase
{
  public function testRankFusionPrefersDocumentsHighInBothRankings(): void
  {
    // b is second in both, a and c are first in one each.
    $scores = DocsSearch::fuse([['a', 'b'], ['c', 'b']]);

    $this->assertSame(['b', 'a', 'c'], array_keys($scores));
    $this->assertEqualsWithDelta(2 / 62, $scores['b'], 1e-12);
    $this->assertSame([], DocsSearch::fuse([[]]));
  }

  public function testKeywordsOnlyWithoutEmbeddings(): void
  {
    $index = $this->createMock(Index::class);
    $index->expects($this->once())->method('search')
      ->with($this->callback(static fn (array $query): bool => isset($query['query']['bool'])))
      ->willReturn($this->results([self::hit('guide', 'en', 'tutorials/jump', 'Make your cat jump')]))
    ;

    $result = $this->search($index, null)->search('cat jump', 'en');

    $this->assertFalse($result['semantic']);
    $this->assertSame(['tutorials/jump'], array_column($result['groups']['guide'], 'path'));
    $this->assertSame([], $result['groups']['brick']);
  }

  public function testHybridAddsWhatOnlyTheMeaningFindsAndShowsAPageOnceInTheBestLocale(): void
  {
    $index = $this->createStub(Index::class);
    $index->method('search')->willReturnCallback(fn (array $query): ResultSet => isset($query['query']['knn'])
      ? $this->results([
        self::hit('brick', 'en', 'reference/bricks/ChangeYByNBrick', 'Change Y by'),
        self::hit('guide', 'en', 'tutorials/jump', 'Make your cat jump'),
      ])
      : $this->results([
        self::hit('guide', 'en', 'tutorials/jump', 'Make your cat jump'),
        self::hit('guide', 'de_DE', 'tutorials/jump', 'Lass deine Katze springen'),
      ]));

    $result = $this->search($index, array_fill(0, DocsEmbedder::DIMS, 0.1))->search('Katze springen', 'de');

    $this->assertTrue($result['semantic']);
    $this->assertSame([['type' => 'guide', 'path' => 'tutorials/jump', 'locale' => 'de_DE', 'title' => 'Lass deine Katze springen', 'snippet' => '']], $result['groups']['guide']);
    $this->assertSame(['reference/bricks/ChangeYByNBrick'], array_column($result['groups']['brick'], 'path'));
  }

  public function testSearchesWithoutResultsAreLoggedWithoutWhoAsked(): void
  {
    $index = $this->createStub(Index::class);
    $index->method('search')->willReturn($this->results([]));
    $log = $this->createMock(LoggerInterface::class);
    $log->expects($this->once())->method('info')->with('docs_search_no_results', ['query' => 'zzyzx', 'locale' => 'de']);

    $search = new DocsSearch($this->indexes($index), $this->embedder(null), new DocsLibrary($this->createStub(DocPageRepository::class)), $log, new NullLogger());
    $search->search(' zzyzx ', 'de');
  }

  public function testEmbedderWithoutUrlAsksNobody(): void
  {
    $http = new MockHttpClient(function (): never {
      $this->fail('no request expected');
    });
    $embedder = new DocsEmbedder($http, new ArrayAdapter(), new NullLogger(), '');

    $this->assertNull($embedder->embedQuery('make my cat jump'));
    $this->assertNull($embedder->embedPassages(['text']));
  }

  public function testEmbedderFailureMeansKeywordSearch(): void
  {
    $calls = 0;
    $http = new MockHttpClient(static function () use (&$calls): MockResponse {
      ++$calls;

      return new MockResponse('down', ['http_code' => 503]);
    });
    $embedder = new DocsEmbedder($http, new ArrayAdapter(), new NullLogger(), 'http://embeddings');

    $this->assertNull($embedder->embedQuery('make my cat jump'));
    $this->assertNull($embedder->embedQuery('make my cat jump'));
    $this->assertSame(1, $calls, 'a failure is cached briefly, so a down server is not asked on every keystroke');
    $this->assertNull($embedder->embedPassages(['text']));
  }

  public function testEmbedderSendsE5PrefixesAndCachesQueries(): void
  {
    $requests = [];
    $http = new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests): MockResponse {
      $requests[] = [$url, json_decode($options['body'], true)['inputs']];

      return new MockResponse((string) json_encode([array_fill(0, DocsEmbedder::DIMS, 0.5)]));
    });
    $embedder = new DocsEmbedder($http, new ArrayAdapter(), new NullLogger(), 'http://embeddings/');

    $this->assertCount(DocsEmbedder::DIMS, $embedder->embedQuery('make my cat jump') ?? []);
    $embedder->embedQuery(' make my cat jump ');
    $embedder->embedPassages(['Change Y by']);

    $this->assertSame([['http://embeddings/embed', ['query: make my cat jump']], ['http://embeddings/embed', ['passage: Change Y by']]], $requests);
  }

  /** @param list<float>|null $vector */
  private function search(Index $index, ?array $vector): DocsSearch
  {
    return new DocsSearch($this->indexes($index), $this->embedder($vector), new DocsLibrary($this->createStub(DocPageRepository::class)), new NullLogger(), new NullLogger());
  }

  private function indexes(Index $index): IndexManager
  {
    $indexes = $this->createStub(IndexManager::class);
    $indexes->method('getIndex')->willReturn($index);

    return $indexes;
  }

  /** @param list<float>|null $vector */
  private function embedder(?array $vector): DocsEmbedder
  {
    $embedder = $this->createStub(DocsEmbedder::class);
    $embedder->method('embedQuery')->willReturn($vector);

    return $embedder;
  }

  /** @param list<Result> $hits */
  private function results(array $hits): ResultSet
  {
    $results = $this->createStub(ResultSet::class);
    $results->method('getResults')->willReturn($hits);

    return $results;
  }

  private static function hit(string $type, string $locale, string $path, string $title): Result
  {
    return new Result(['_id' => $type.':'.$locale.':'.$path, '_score' => 1.0, '_source' => ['type' => $type, 'path' => $path, 'locale' => $locale, 'title' => $title, 'snippet' => '']]);
  }
}
