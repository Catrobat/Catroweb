<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\SearchApi;
use App\Api\Services\Search\SearchApiFacade;
use App\Project\Scratch\ScratchSearch;
use OpenAPI\Server\Model\SearchResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(SearchApi::class)]
final class SearchApiTest extends TestCase
{
  /**
   * @throws \JsonException
   * @throws Exception
   */
  #[Group('unit')]
  public function testSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->createSearchApi($this->createStub(SearchApiFacade::class))->searchGet('query', 'type', 20, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(SearchResponse::class, $response);
  }

  /**
   * Scratch's live search is slow when not cached, so the default search must not wait for it.
   *
   * @throws \JsonException
   * @throws Exception
   */
  #[Group('unit')]
  public function testDefaultSearchDoesNotAskScratch(): void
  {
    $scratch_search = $this->createMock(ScratchSearch::class);
    $scratch_search->expects($this->never())->method('search');
    $facade = $this->createStub(SearchApiFacade::class);
    $facade->method('getScratchSearch')->willReturn($scratch_search);
    $response_code = 200;
    $response_headers = [];

    $this->createSearchApi($facade)->searchGet('query', 'all', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * The search page sends type=scratch next to every search: it must not use up the budget of the other searches.
   *
   * @throws \JsonException
   * @throws Exception
   */
  #[Group('unit')]
  public function testScratchSearchHasItsOwnRateLimit(): void
  {
    $scratch_search = $this->createStub(ScratchSearch::class);
    $scratch_search->method('search')->willReturn(['projects' => [], 'total' => 0]);
    $facade = $this->createStub(SearchApiFacade::class);
    $facade->method('getScratchSearch')->willReturn($scratch_search);
    $search_api = $this->createSearchApi($facade, 1);

    $codes = [];
    foreach (['all', 'scratch', 'all', 'scratch'] as $type) {
      $response_code = 200;
      $response_headers = [];
      $search_api->searchGet('query', $type, 20, null, $response_code, $response_headers);
      $codes[] = $response_code;
    }

    $this->assertSame([Response::HTTP_OK, Response::HTTP_OK, Response::HTTP_TOO_MANY_REQUESTS, Response::HTTP_TOO_MANY_REQUESTS], $codes);
  }

  /**
   * @throws Exception
   */
  private function createSearchApi(SearchApiFacade $facade, ?int $limit = null): SearchApi
  {
    $config = null === $limit ? ['policy' => 'no_limit'] : ['policy' => 'fixed_window', 'limit' => $limit, 'interval' => '1 minute'];

    return new SearchApi(
      $facade,
      new RateLimiterFactory(['id' => 'search'] + $config, new InMemoryStorage()),
      new RateLimiterFactory(['id' => 'search_scratch'] + $config, new InMemoryStorage()),
      $this->createStub(RequestStack::class),
    );
  }
}
