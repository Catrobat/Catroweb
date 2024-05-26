<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\SearchApi;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Search\SearchApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Api\SearchApiInterface;
use OpenAPI\Server\Model\SearchResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\SearchApi
 */
final class SearchApiTest extends DefaultTestCase
{
  protected MockObject|SearchApi $object;

  protected MockObject|SearchApiFacade $facade;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(SearchApiFacade::class);
    $this->mockProperty(SearchApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(SearchApi::class));
    $this->assertInstanceOf(SearchApi::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(SearchApiInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new SearchApi($this->facade);
    $this->assertInstanceOf(SearchApi::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\SearchApi::searchGet
   */
  public function testSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->object->searchGet('query', 'type', 20, 0, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(SearchResponse::class, $response);
  }
}
