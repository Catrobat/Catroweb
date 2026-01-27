<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\User\UserApiFacade;
use App\Api\Services\User\UserApiLoader;
use App\Api\Services\User\UserApiProcessor;
use App\Api\Services\User\UserRequestValidator;
use App\Api\Services\User\UserResponseManager;
use App\Api\Services\ValidationWrapper;
use App\Api\UserApi;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(UserApi::class)]
final class UserApiTest extends DefaultTestCase
{
  protected UserApi $object;

  protected Stub|UserApiFacade $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(UserApiFacade::class);

    // Setup ResponseManager stub
    $response_manager = $this->createStub(UserResponseManager::class);
    $response_manager->method('createExtendedUserDataResponse')->willReturn($this->createStub(ExtendedUserDataResponse::class));
    $response_manager->method('createBasicUserDataResponse')->willReturn($this->createStub(BasicUserDataResponse::class));
    $response_manager->method('createUsersDataResponse')->willReturn([]);
    $response_manager->method('createUserRegisteredResponse')->willReturn($this->createStub(JWTResponse::class));
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    // Setup Processor stub
    $processor = $this->createStub(UserApiProcessor::class);
    $processor->method('registerUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getProcessor')->willReturn($processor);

    // Setup AuthenticationManager stub
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $authentication_manager->method('createAuthenticationTokenFromUser')->willReturn('token');
    $authentication_manager->method('createRefreshTokenByUser')->willReturn('refresh_token');
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object = new UserApi($this->facade);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserPostDryRun(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createStub(RegisterRequest::class);
    $register_request->method('isDryRun')->willReturn(true);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserPostValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(true);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createStub(RegisterRequest::class);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);

    $this->assertInstanceOf(RegisterErrorResponse::class, $response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createStub(RegisterRequest::class);
    $register_request->method('isDryRun')->willReturn(false);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_CREATED, $response_code);

    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object->userDelete($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->userGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(ExtendedUserDataResponse::class, $response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(UserApiLoader::class);
    $loader->method('findUserByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->userIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(UserApiLoader::class);
    $loader->method('findUserByID')->willReturn($this->createStub(User::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->userIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(BasicUserDataResponse::class, $response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUsersSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(UserApiLoader::class);
    $loader->method('searchUsers')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->usersSearchGet('query', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserPutDryRun(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $update_user_request = $this->createStub(UpdateUserRequest::class);
    $update_user_request->method('isDryRun')->willReturn(true);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testUserPutValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(true);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $update_user_request = $this->createStub(UpdateUserRequest::class);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);

    $this->assertInstanceOf(UpdateUserErrorResponse::class, $response);
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  #[Group('unit')]
  public function testUserPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createStub(UserRequestValidator::class);
    $validator_wrapper = $this->createStub(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getRequestValidator')->willReturn($request_validator);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $update_user_request = $this->createStub(UpdateUserRequest::class);
    $update_user_request->method('isDryRun')->willReturn(false);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);

    $this->assertNull($response);
  }
}
