<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Projects\ProjectsApiProcessor;
use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Api\Services\Projects\ProjectsResponseManager;
use App\Api\Services\ValidationWrapper;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use App\System\Testing\PhpUnit\DefaultTestCase;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\ProjectReportRequest;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\UpdateProjectErrorResponse;
use OpenAPI\Server\Model\UpdateProjectFailureResponse;
use OpenAPI\Server\Model\UpdateProjectRequest;
use OpenAPI\Server\Model\UploadErrorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
final class ProjectsApiTest extends DefaultTestCase
{
  protected MockObject|ProjectsApi $object;

  protected MockObject|ProjectsApiFacade $facade;

  protected mixed $full_validator;

  protected mixed $full_response_manager;

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsApi::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getAuthenticationToken'])
      ->getMock()
    ;

    $this->facade = $this->createMock(ProjectsApiFacade::class);
    $this->mockProperty(ProjectsApi::class, $this->object, 'facade', $this->facade);

    ProjectsApiTest::bootKernel();
    $this->full_validator = ProjectsApiTest::getContainer()->get(ProjectsRequestValidator::class);
    $this->full_response_manager = ProjectsApiTest::getContainer()->get(ProjectsResponseManager::class);
  }

  #[Group('integration')]
  public function testCtor(): void
  {
    $this->object = new ProjectsApi($this->facade);
    $this->assertInstanceOf(ProjectsApi::class, $this->object);
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testProjectIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createMock(Program::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  private function projectIdPut_setLoaderAndAuthManager(MockObject|Program|null $project = null, MockObject|User|null $user = null): void
  {
    if (is_null($user)) {
      $user = $this->createMock(User::class);
    }

    if (is_null($project)) {
      $project = $this->createMock(Program::class);
      $project->method('getUser')->willReturn($user);
    }

    $this->projectIdPut_setLoader($project);
    $this->projectIdPut_setAuthManager($user);
  }

  /**
   * @throws Exception
   */
  private function projectIdPut_setLoader(MockObject|Program|null $project): void
  {
    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($project);
    $this->facade->method('getLoader')->willReturn($loader);
  }

  /**
   * @throws Exception
   */
  private function projectIdPut_setAuthManager(MockObject|User|null $user): void
  {
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $project = new Program();
    $user = new User();
    $project->setId('id');
    $project->setName('Old name');
    $project->setDescription('Old description');
    $project->setCredits('Old credits');
    $project->setPrivate(false);
    $project->setUser($user);

    $this->projectIdPut_setLoaderAndAuthManager($project, $user);

    $extracted_file_repository = $this->createMock(ExtractedFileRepository::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn(null);
    $processor = new ProjectsApiProcessor(
      $this->createMock(ProjectManager::class),
      $this->createMock(EntityManagerInterface::class),
      $extracted_file_repository,
      $this->createMock(ProjectFileRepository::class),
      $this->createMock(ScreenshotRepository::class)
    );
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->facade->method('getRequestValidator')->willReturn($this->full_validator);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $project_name = 'My special 🐼 project!';
    $project_description = 'Integer lobortis lacus efficitur arcu blandit hendrerit. In hac habitasse platea accumsan.';
    $project_credits = 'THANKS :) Sed ut ligula lectus. Integer dui augue.';

    $update_project_request->method('getName')->willReturn($project_name);
    $update_project_request->method('getDescription')->willReturn($project_description);
    $update_project_request->method('getCredits')->willReturn($project_credits);
    $update_project_request->method('isPrivate')->willReturn(true);

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
    $this->assertSame($project_name, $project->getName(), 'Project name not changed');
    $this->assertSame($project_description, $project->getDescription(), 'Project description not changed');
    $this->assertSame($project_credits, $project->getCredits(), 'Project credits not changed');
    $this->assertSame(true, $project->getPrivate(), 'Project not set to private');
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectIdPut_setLoader(null);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectIdPut_setLoader($this->createMock(Program::class));
    $this->projectIdPut_setAuthManager(null);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $project = new Program();
    $user = new User();
    $user->setId('user1');

    $wrong_user = new User();
    $wrong_user->setId('user2');

    $project->setUser($wrong_user);

    $this->projectIdPut_setLoaderAndAuthManager($project, $user);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectIdPut_setLoaderAndAuthManager();

    $this->facade->method('getRequestValidator')->willReturn($this->full_validator);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $project_name = '';
    $project_description = str_pad('a', 10_001, 'a');
    $project_credits = str_pad('a', 3_001, 'a');
    $project_screenshot = 'data:image/nonsense;base64,deadBEEF===';

    $update_project_request->method('getName')->willReturn($project_name);
    $update_project_request->method('getDescription')->willReturn($project_description);
    $update_project_request->method('getCredits')->willReturn($project_credits);
    $update_project_request->method('getScreenshot')->willReturn($project_screenshot);

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(UpdateProjectErrorResponse::class, $response);
    $this->assertStringContainsString('empty', $response->getName(), 'Name Validation failed');
    $this->assertStringContainsString('long', $response->getDescription(), 'Description Validation failed');
    $this->assertStringContainsString('long', $response->getCredits(), 'Credits Validation failed');
    $this->assertStringContainsString('invalid', $response->getScreenshot(), 'Screenshot Validation failed');
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutSaveXMLError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectIdPut_setLoaderAndAuthManager();

    $extracted_file_repository = $this->createMock(ExtractedFileRepository::class);
    $extracted_file = $this->createMock(ExtractedCatrobatFile::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn($extracted_file);
    $extracted_file_repository->method('saveProjectExtractedFile')->willThrowException(new \Exception(''));

    $processor = new ProjectsApiProcessor(
      $this->createMock(ProjectManager::class),
      $this->createMock(EntityManagerInterface::class),
      $extracted_file_repository,
      $this->createMock(ProjectFileRepository::class),
      $this->createMock(ScreenshotRepository::class)
    );
    $this->facade->method('getProcessor')->willReturn($processor);

    $validator = $this->createMock(ProjectsRequestValidator::class);
    $validation_wrapper = $this->createMock(ValidationWrapper::class);
    $validation_wrapper->method('hasError')->willReturn(false);
    $validator->method('validateUpdateRequest')->willReturn($validation_wrapper);

    $this->facade->method('getRequestValidator')->willReturn($validator);
    $this->facade->method('getResponseManager')->willReturn($this->full_response_manager);

    $update_project_request = $this->createMock(UpdateProjectRequest::class);

    $update_project_request->method('getName')->willReturn('New name');

    $response = $this->object->projectIdPut('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response_code);
    $this->assertInstanceOf(UpdateProjectFailureResponse::class, $response);
    $this->assertNotEmpty($response->getError());
    $this->assertStringContainsString('Failed saving', $response->getError());
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsFeaturedGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('getFeaturedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->projectsFeaturedGet('', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsGet('category', 'en', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectIdRecommendationsGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectIdRecommendationsGet('id', 'category', 'en', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectIdRecommendationsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createMock(Program::class));
    $loader->method('getRecommendedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdRecommendationsGet('id', 'category', 'en', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsSearchGet('query', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsCategoriesGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsCategoriesGet('', '', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsUserGetForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet('', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsUserGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('getId')->willReturn('1');
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet('', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsUserIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(true);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsUserIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $request_validator = $this->createMock(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(false);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', '', 20, 0, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectIdReportPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $project_report_request = $this->createMock(ProjectReportRequest::class);

    $this->object->projectIdReportPost('id', $project_report_request, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_IMPLEMENTED, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsPost(): void
  {
    $response_code = 200;
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
    $response = $this->object->projectsPost('checksum', $file, 'en', '', false, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CREATED, $response_code);
    $this->assertArrayHasKey('Location', $response_headers);
    $this->assertInstanceOf(ProjectResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsPostValidationError(): void
  {
    $response_code = 200;
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
    $response = $this->object->projectsPost('checksum', $file, 'en', '', false, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(UploadErrorResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsPostAddException(): void
  {
    $response_code = 200;
    $response_headers = [];

    $processor = $this->createMock(ProjectsApiProcessor::class);
    $processor->method('addProject')->willThrowException(new \Exception());
    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('isVerified')->willReturn(true);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createMock(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, 'en', '', false, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(UploadErrorResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectIdDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectIdDelete('id', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsExtensionsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsExtensionsGet('en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsTagsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsTagsGet('', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }
}
