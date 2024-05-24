<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\AuthenticationApi;
use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Authentication\AuthenticationApiLoader;
use App\Api\Services\Authentication\AuthenticationApiProcessor;
use App\Api\Services\Base\AbstractApiController;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use OpenAPI\Server\Model\UpgradeTokenRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\AuthenticationApi
 */
final class AuthenticationApiTest extends DefaultTestCase
{
  protected AuthenticationApi|MockObject $object;

  protected AuthenticationApiFacade|MockObject $facade;

  /**
   * @throws \ReflectionException
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(AuthenticationApiFacade::class);
    $this->mockProperty(AuthenticationApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(AuthenticationApi::class));
    $this->assertInstanceOf(AuthenticationApi::class, $this->object);
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
    $this->assertInstanceOf(AuthenticationApiInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new AuthenticationApi($this->facade);
    $this->assertInstanceOf(AuthenticationApi::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationGet
   */
  public function testAuthenticationGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->authenticationGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationPost
   */
  public function testAuthenticationPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $login_request = $this->createMock(LoginRequest::class);
    $response = $this->object->authenticationPost($login_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationDelete
   */
  public function testAuthenticationDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $processor = $this->createMock(AuthenticationApiProcessor::class);
    $processor->method('deleteRefreshToken')->willReturn(true);
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->object->authenticationDelete('', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationRefreshPost
   */
  public function testAuthenticationRefreshPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $refresh_request = $this->createMock(RefreshRequest::class);
    $response = $this->object->authenticationRefreshPost($refresh_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationUpgradePost
   */
  public function testAuthenticationUpgradePost400(): void
  {
    $response_code = 200;
    $response_headers = [];

    $upgrade_token_request = $this->createMock(UpgradeTokenRequest::class);
    $upgrade_token_request->method('getUploadToken')->willReturn('');
    $response = $this->object->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationUpgradePost
   */
  public function testAuthenticationUpgradePost401(): void
  {
    $response_code = 200;
    $response_headers = [];

    $upgrade_token_request = $this->createMock(UpgradeTokenRequest::class);
    $upgrade_token_request->method('getUploadToken')->willReturn('123456');
    $response = $this->object->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\AuthenticationApi::authenticationUpgradePost
   */
  public function testAuthenticationUpgradePost200(): void
  {
    $response_code = 200;
    $response_headers = [];

    $upgrade_token_request = $this->createMock(UpgradeTokenRequest::class);
    $upgrade_token_request->method('getUploadToken')->willReturn('123456');
    $loader = $this->createMock(AuthenticationApiLoader::class);
    $user = $this->createMock(User::class);
    $loader->method('findUserByUploadToken')->willReturn($user);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }
}
