<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Projects\ProjectsApiProcessor;
use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\Entity\Program;
use App\Entity\User;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\ProjectReportRequest;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\UploadErrorResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\ProjectsApi
 */
final class ProjectsApiTest extends CatrowebTestCase
{
  /**
   * @var ProjectsApi|MockObject
   */
  protected $object;

  /**
   * @var ProjectsApiFacade|MockObject
   */
  protected $facade;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsApi::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getAuthenticationToken'])
      ->getMock()
    ;

    $this->facade = $this->createMock(ProjectsApiFacade::class);
    $this->mockProperty(ProjectsApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(ProjectsApi::class));
    $this->assertInstanceOf(ProjectsApi::class, $this->object);
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
    $this->assertInstanceOf(ProjectsApiInterface::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new ProjectsApi($this->facade);
    $this->assertInstanceOf(ProjectsApi::class, $this->object);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdGet
   *
   * @throws Exception
   */
  public function testProjectIdGetNotFound(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdGet
   *
   * @throws Exception
   */
  public function testProjectIdGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createMock(Program::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectResponse::class, $response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsFeaturedGet
   *
   * @throws Exception
   */
  public function testProjectsFeaturedGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('getFeaturedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectsFeaturedGet(null, null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsGet
   *
   * @throws Exception
   */
  public function testProjectsGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->projectsGet('category', null, null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdRecommendationsGet
   *
   * @throws Exception
   */
  public function testProjectIdRecommendationsGetNotFound(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->projectIdRecommendationsGet('id', 'category', null, null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdRecommendationsGet
   *
   * @throws Exception
   */
  public function testProjectIdRecommendationsGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createMock(Program::class));
    $loader->method('getRecommendedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdRecommendationsGet('id', 'category', null, null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsSearchGet
   *
   * @throws Exception
   */
  public function testProjectsSearchGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->projectsSearchGet('query', null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsCategoriesGet
   *
   * @throws Exception
   */
  public function testProjectsCategoriesGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->projectsCategoriesGet(null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsUserGet
   *
   * @throws Exception
   */
  public function testProjectsUserGetForbidden(): void
  {
    $response_code = null;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getUserFromAuthenticationToken')->willReturn(null);
    $this->object->method('getAuthenticationToken')->willReturn('');
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet(null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsUserGet
   *
   * @throws Exception
   */
  public function testProjectsUserGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('getId')->willReturn('1');
    $authentication_manager->method('getUserFromAuthenticationToken')->willReturn($user);
    $this->object->method('getAuthenticationToken')->willReturn('');
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet(null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsUserIdGet
   *
   * @throws Exception
   */
  public function testProjectsUserIdGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $request_validator = $this->createMock(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(true);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsUserIdGet
   *
   * @throws Exception
   */
  public function testProjectsUserIdGetNotFound(): void
  {
    $response_code = null;
    $response_headers = [];

    $request_validator = $this->createMock(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(false);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', null, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdReportPost
   *
   * @throws Exception
   */
  public function testProjectIdReportPost(): void
  {
    $response_code = null;
    $response_headers = [];

    $project_report_request = $this->createMock(ProjectReportRequest::class);

    $response = $this->object->projectIdReportPost('id', $project_report_request, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_IMPLEMENTED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsPost
   *
   * @throws Exception
   */
  public function testProjectsPost(): void
  {
    $response_code = null;
    $response_headers = [];

    $user = $this->createMock(User::class);
    $user->method('isVerified')->willReturn(true);
    $processor = $this->createMock(ProjectsApiProcessor::class);
    $processor->method('addProject')->willReturn($this->createMock(Program::class));
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createMock(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_CREATED, $response_code);
    $this->assertArrayHasKey('Location', $response_headers);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsPost
   *
   * @throws Exception
   */
  public function testProjectsPostValidationError(): void
  {
    $response_code = null;
    $response_headers = [];

    $validator = $this->createMock(ProjectsRequestValidator::class);
    $validation_wrapper = $this->createMock(ValidationWrapper::class);
    $validation_wrapper->method('hasError')->willReturn(true);
    $validator->method('validateUploadFile')->willReturn($validation_wrapper);
    $processor = $this->createMock(ProjectsApiProcessor::class);
    $processor->method('addProject')->willReturn($this->createMock(Program::class));
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('isVerified')->willReturn(true);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);
    $this->facade->method('getRequestValidator')->willReturn($validator);

    $file = $this->createMock(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(UploadErrorResponse::class, $response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsPost
   *
   * @throws Exception
   */
  public function testProjectsPostAddException(): void
  {
    $response_code = null;
    $response_headers = [];

    $processor = $this->createMock(ProjectsApiProcessor::class);
    $processor->method('addProject')->willThrowException(new Exception());
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('isVerified')->willReturn(true);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createMock(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(UploadErrorResponse::class, $response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectsPost
   *
   * @throws Exception
   */
  public function testProjectsPostAddExceptionForbidden(): void
  {
    $response_code = null;
    $response_headers = [];

    $processor = $this->createMock(ProjectsApiProcessor::class);
    $processor->method('addProject')->willThrowException(new Exception());
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('isVerified')->willReturn(false);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createMock(UploadedFile::class);
    $this->object->projectsPost('checksum', $file, null, null, null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_FORBIDDEN, $response_code);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\ProjectsApi::projectIdDelete
   *
   * @throws Exception
   */
  public function testProjectIdDelete(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->projectIdDelete('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_IMPLEMENTED, $response_code);
    $this->assertNull($response);
  }
}
