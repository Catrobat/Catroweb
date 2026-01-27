<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\SearchApi;
use App\Api\Services\Search\SearchApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\SearchResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(SearchApi::class)]
final class SearchApiTest extends DefaultTestCase
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
    $this->search_api = new SearchApi($this->facade);
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
