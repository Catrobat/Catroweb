<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\SearchApi;
use App\Api\Services\Search\SearchApiFacade;
use OpenAPI\Server\Model\SearchResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
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
  protected SearchApi $search_api;

  protected SearchApiFacade|Stub $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(SearchApiFacade::class);
    $this->search_api = new SearchApi(
      $this->facade,
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
      $this->createStub(RequestStack::class),
    );
  }

  /**
   * @throws \JsonException
   */
  #[Group('unit')]
  public function testSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->search_api->searchGet('query', 'type', 20, 0, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(SearchResponse::class, $response);
  }
}
