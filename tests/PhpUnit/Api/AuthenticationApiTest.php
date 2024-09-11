<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\AuthenticationApi;
use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Authentication\AuthenticationApiLoader;
use App\Api\Services\Authentication\AuthenticationApiProcessor;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use OpenAPI\Server\Model\UpgradeTokenRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(AuthenticationApi::class)]
final class AuthenticationApiTest extends DefaultTestCase
{
  protected AuthenticationApi $authentication_api;

  protected AuthenticationApiFacade|MockObject $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createMock(AuthenticationApiFacade::class);
    $this->authentication_api = new AuthenticationApi($this->facade);
  }

  #[Group('unit')]
  public function testAuthenticationGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->authentication_api->authenticationGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testAuthenticationPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $login_request = $this->createMock(LoginRequest::class);
    $response = $this->authentication_api->authenticationPost($login_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testAuthenticationDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $processor = $this->createMock(AuthenticationApiProcessor::class);
    $processor->method('deleteRefreshToken')->willReturn(true);
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->authentication_api->authenticationDelete('', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testAuthenticationRefreshPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $refresh_request = $this->createMock(RefreshRequest::class);
    $response = $this->authentication_api->authenticationRefreshPost($refresh_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testAuthenticationUpgradePost400(): void
  {
    $response_code = 200;
    $response_headers = [];

    $upgrade_token_request = $this->createMock(UpgradeTokenRequest::class);
    $upgrade_token_request->method('getUploadToken')->willReturn('');
    $response = $this->authentication_api->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testAuthenticationUpgradePost401(): void
  {
    $response_code = 200;
    $response_headers = [];

    $upgrade_token_request = $this->createMock(UpgradeTokenRequest::class);
    $upgrade_token_request->method('getUploadToken')->willReturn('123456');
    $response = $this->authentication_api->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
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

    $response = $this->authentication_api->authenticationUpgradePost($upgrade_token_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }
}
