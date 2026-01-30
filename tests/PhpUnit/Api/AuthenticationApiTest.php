<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\AuthenticationApi;
use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Authentication\AuthenticationApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(AuthenticationApi::class)]
final class AuthenticationApiTest extends DefaultTestCase
{
  protected AuthenticationApi $authentication_api;

  protected AuthenticationApiFacade|Stub $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(AuthenticationApiFacade::class);
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

    $login_request = $this->createStub(LoginRequest::class);
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

    $processor = $this->createStub(AuthenticationApiProcessor::class);
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

    $refresh_request = $this->createStub(RefreshRequest::class);
    $response = $this->authentication_api->authenticationRefreshPost($refresh_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(JWTResponse::class, $response);
  }
}
