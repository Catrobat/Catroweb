<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\User\UserApiFacade;
use App\Api\Services\User\UserApiLoader;
use App\Api\Services\User\UserRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\Api\UserApi;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\UserApi
 */
final class UserApiTest extends DefaultTestCase
{
  protected MockObject|UserApi $object;

  protected MockObject|UserApiFacade $facade;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(UserApiFacade::class);
    $this->mockProperty(UserApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(UserApi::class));
    $this->assertInstanceOf(UserApi::class, $this->object);
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
    $this->assertInstanceOf(UserApiInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new UserApi($this->facade);
    $this->assertInstanceOf(UserApi::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPost
   *
   * @throws \Exception
   */
  public function testUserPostDryRun(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createMock(RegisterRequest::class);
    $register_request->method('isDryRun')->willReturn(true);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPost
   *
   * @throws \Exception
   */
  public function testUserPostValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(true);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createMock(RegisterRequest::class);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);

    $this->assertInstanceOf(RegisterErrorResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPost
   *
   * @throws \Exception
   */
  public function testUserPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateRegistration')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $register_request = $this->createMock(RegisterRequest::class);
    $register_request->method('isDryRun')->willReturn(false);

    $response = $this->object->userPost($register_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_CREATED, $response_code);

    $this->assertInstanceOf(JWTResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userDelete
   *
   * @throws \Exception
   */
  public function testUserDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object->userDelete($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userGet
   *
   * @throws \Exception
   */
  public function testUserGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->userGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(ExtendedUserDataResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userIdGet
   *
   * @throws \Exception
   */
  public function testUserIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UserApiLoader::class);
    $loader->method('findUserByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->userIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userIdGet
   *
   * @throws \Exception
   */
  public function testUserIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UserApiLoader::class);
    $loader->method('findUserByID')->willReturn($this->createMock(User::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->userIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(BasicUserDataResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::usersSearchGet
   *
   * @throws \Exception
   */
  public function testUsersSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UserApiLoader::class);
    $loader->method('searchUsers')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->usersSearchGet('query', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPut
   *
   * @throws \Exception
   */
  public function testUserPutDryRun(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $update_user_request = $this->createMock(UpdateUserRequest::class);
    $update_user_request->method('isDryRun')->willReturn(true);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPut
   *
   * @throws \Exception
   */
  public function testUserPutValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(true);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $update_user_request = $this->createMock(UpdateUserRequest::class);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);

    $this->assertInstanceOf(UpdateUserErrorResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UserApi::userPut
   *
   * @throws \Exception
   */
  public function testUserPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(UserRequestValidator::class);
    $validator_wrapper = $this->createMock(ValidationWrapper::class);
    $validator_wrapper->method('hasError')->willReturn(false);
    $request_validator->method('validateUpdateRequest')->willReturn($validator_wrapper);
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getRequestValidator')->willReturn($request_validator);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $update_user_request = $this->createMock(UpdateUserRequest::class);
    $update_user_request->method('isDryRun')->willReturn(false);

    $response = $this->object->userPut($update_user_request, 'de', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);

    $this->assertNull($response);
  }
}
