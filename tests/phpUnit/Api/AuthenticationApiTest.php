<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api;

use App\Api\AuthenticationApi;
use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Base\AbstractApiController;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\AuthenticationApi
 */
final class AuthenticationApiTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationApi|MockObject
   */
  protected $object;

  /**
   * @var AuthenticationApiFacade|MockObject
   */
  protected $facade;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->getMockBuilder(AuthenticationApiFacade::class)->disableOriginalConstructor()->getMock();
    $this->mockProperty(AuthenticationApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(AuthenticationApi::class));
    $this->assertInstanceOf(AuthenticationApi::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(AuthenticationApiInterface::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new AuthenticationApi($this->facade);
    $this->assertInstanceOf(AuthenticationApi::class, $this->object);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\AuthenticationApi::authenticationGet
   */
  public function testAuthenticationGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $this->object->authenticationGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\AuthenticationApi::authenticationPost
   */
  public function testAuthenticationPost(): void
  {
    $response_code = null;
    $response_headers = [];

    $login_request = $this->createMock(LoginRequest::class);
    $response = $this->object->authenticationPost($login_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\AuthenticationApi::authenticationDelete
   */
  public function testAuthenticationDelete(): void
  {
    $response_code = null;
    $response_headers = [];

    $this->object->authenticationDelete('', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_IMPLEMENTED, $response_code);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\AuthenticationApi::authenticationRefreshPost
   */
  public function testAuthenticationRefreshPost(): void
  {
    $response_code = null;
    $response_headers = [];

    $refresh_request = $this->createMock(RefreshRequest::class);
    $response = $this->object->authenticationRefreshPost($refresh_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_IMPLEMENTED, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }
}
