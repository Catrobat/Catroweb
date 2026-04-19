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
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\Api\Services\ValidationWrapper;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Moderation\TextSanitizer;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\ProjectManager;
use App\Project\Remix\RemixManager;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsListResponse;
use OpenAPI\Server\Model\UpdateProjectRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
final class ProjectsApiTest extends KernelTestCase
{
  protected ProjectsApi $object;

  protected Stub&ProjectsApiFacade $facade;

  protected Stub&ReactionsApiFacade $reactions_facade;

  protected mixed $full_validator;

  protected mixed $full_response_manager;

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(ProjectsApiFacade::class);
    $this->reactions_facade = $this->createStub(ReactionsApiFacade::class);
    $this->object = new ProjectsApi(
      $this->facade,
      $this->reactions_facade,
      $this->createStub(CodeTreeBuilder::class),
      $this->createStub(RemixManager::class),
      $this->createStub(ScreenshotRepository::class),
      $this->createNoLimitRateLimiterFactory('phpunit_projects_upload_daily'),
      $this->createNoLimitRateLimiterFactory('phpunit_projects_reaction_burst'),
      $this->createNoLimitRateLimiterFactory('phpunit_projects_download_burst'),
      new \Symfony\Component\HttpFoundation\RequestStack(),
      new \Psr\Log\NullLogger(),
    );

    ProjectsApiTest::bootKernel();
    $this->full_validator = ProjectsApiTest::getContainer()->get(ProjectsRequestValidator::class);
    $this->full_response_manager = ProjectsApiTest::getContainer()->get(ProjectsResponseManager::class);
  }

  private function createNoLimitRateLimiterFactory(string $id): RateLimiterFactory
  {
    return new RateLimiterFactory(
      [
        'id' => $id,
        'policy' => 'no_limit',
      ],
      new InMemoryStorage(),
    );
  }

  /**
   * @throws \Exception|Exception
   */
  #[Group('unit')]
  public function testProjectsIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectsIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectsIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createStub(Project::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectsIdGet('id', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  private function createPassthroughTextSanitizer(): TextSanitizer
  {
    $stub = $this->createStub(TextSanitizer::class);
    $stub->method('sanitize')->willReturnArgument(0);
    $stub->method('sanitizeWithLocale')->willReturnArgument(0);

    return $stub;
  }

  private function projectsIdPatch_setLoaderAndAuthManager(MockObject|Project|null $project = null, MockObject|User|null $user = null): void
  {
    if (is_null($user)) {
      $user = $this->createStub(User::class);
    }

    if (is_null($project)) {
      $project = $this->createStub(Project::class);
      $project->method('getUser')->willReturn($user);
    }

    $this->projectsIdPatch_setLoader($project);
    $this->projectsIdPatch_setAuthManager($user);
  }

  /**
   * @throws Exception
   */
  private function projectsIdPatch_setLoader(MockObject|Project|null $project): void
  {
    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($project);
    $this->facade->method('getLoader')->willReturn($loader);
  }

  /**
   * @throws Exception
   */
  private function projectsIdPatch_setAuthManager(MockObject|User|null $user): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
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

    $project = new Project();
    $user = new User();
    $project->setId('id');
    $project->setName('Old name');
    $project->setDescription('Old description');
    $project->setCredits('Old credits');
    $project->setPrivate(false);
    $project->setUser($user);

    $this->projectsIdPatch_setLoaderAndAuthManager($project, $user);

    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn(null);
    $processor = new ProjectsApiProcessor(
      $this->createStub(ProjectManager::class),
      $this->createStub(EntityManagerInterface::class),
      $extracted_file_repository,
      $this->createStub(ProjectFileRepository::class),
      $this->createStub(ScreenshotRepository::class),
      $this->createPassthroughTextSanitizer()
    );
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->facade->method('getRequestValidator')->willReturn($this->full_validator);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $project_name = 'My special 🐼 project!';
    $project_description = 'Integer lobortis lacus efficitur arcu blandit hendrerit. In hac habitasse platea accumsan.';
    $project_credits = 'THANKS :) Sed ut ligula lectus. Integer dui augue.';

    $update_project_request->method('getName')->willReturn($project_name);
    $update_project_request->method('getDescription')->willReturn($project_description);
    $update_project_request->method('getCredits')->willReturn($project_credits);
    $update_project_request->method('isPrivate')->willReturn(true);

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
    $this->assertSame($project_name, $project->getName(), 'Project name not changed');
    $this->assertSame($project_description, $project->getDescription(), 'Project description not changed');
    $this->assertSame($project_credits, $project->getCredits(), 'Project credits not changed');
    $this->assertTrue($project->getPrivate(), 'Project not set to private');
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectsIdPatch_setLoader(null);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

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

    $this->projectsIdPatch_setLoader($this->createStub(Project::class));
    $this->projectsIdPatch_setAuthManager(null);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

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

    $project = new Project();
    $user = new User();
    $user->setId('user1');

    $wrong_user = new User();
    $wrong_user->setId('user2');

    $project->setUser($wrong_user);

    $this->projectsIdPatch_setLoaderAndAuthManager($project, $user);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

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

    $this->projectsIdPatch_setLoaderAndAuthManager();

    $this->facade->method('getRequestValidator')->willReturn($this->full_validator);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $project_name = '';
    $project_description = str_pad('a', 10_001, 'a');
    $project_credits = str_pad('a', 3_001, 'a');
    $project_screenshot = 'data:image/nonsense;base64,deadBEEF===';

    $update_project_request->method('getName')->willReturn($project_name);
    $update_project_request->method('getDescription')->willReturn($project_description);
    $update_project_request->method('getCredits')->willReturn($project_credits);
    $update_project_request->method('getScreenshot')->willReturn($project_screenshot);

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(ErrorResponse::class, $response);
    $error = $response->getError();
    $this->assertNotNull($error);
    $this->assertSame(422, $error->getCode());
    $this->assertSame('validation_error', $error->getType());
    $details = $error->getDetails();
    $this->assertNotNull($details);
    $detail_map = [];
    foreach ($details as $detail) {
      $detail_map[$detail->getField()] = $detail->getMessage();
    }
    $this->assertArrayHasKey('name', $detail_map, 'Name should be in details');
    $this->assertStringContainsString('empty', (string) $detail_map['name'], 'Name Validation failed');
    $this->assertArrayHasKey('description', $detail_map, 'Description should be in details');
    $this->assertStringContainsString('long', (string) $detail_map['description'], 'Description Validation failed');
    $this->assertArrayHasKey('credits', $detail_map, 'Credits should be in details');
    $this->assertStringContainsString('long', (string) $detail_map['credits'], 'Credits Validation failed');
    $this->assertArrayHasKey('screenshot', $detail_map, 'Screenshot should be in details');
    $this->assertStringContainsString('invalid', (string) $detail_map['screenshot'], 'Screenshot Validation failed');
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdPutSaveXMLError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->projectsIdPatch_setLoaderAndAuthManager();

    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $extracted_file = $this->createStub(ExtractedCatrobatFile::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn($extracted_file);
    $extracted_file_repository->method('saveProjectExtractedFile')->willThrowException(new \Exception(''));

    $processor = new ProjectsApiProcessor(
      $this->createStub(ProjectManager::class),
      $this->createStub(EntityManagerInterface::class),
      $extracted_file_repository,
      $this->createStub(ProjectFileRepository::class),
      $this->createStub(ScreenshotRepository::class),
      $this->createPassthroughTextSanitizer()
    );
    $this->facade->method('getProcessor')->willReturn($processor);

    $validator = $this->createStub(ProjectsRequestValidator::class);
    $validation_wrapper = $this->createStub(ValidationWrapper::class);
    $validation_wrapper->method('hasError')->willReturn(false);
    $validator->method('validateUpdateRequest')->willReturn($validation_wrapper);

    $this->facade->method('getRequestValidator')->willReturn($validator);
    $this->facade->method('getResponseManager')->willReturn($this->full_response_manager);

    $update_project_request = $this->createStub(UpdateProjectRequest::class);

    $update_project_request->method('getName')->willReturn('New name');

    $response = $this->object->projectsIdPatch('id', $update_project_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response_code);
    $this->assertInstanceOf(ErrorResponse::class, $response);
    $error = $response->getError();
    $this->assertNotNull($error);
    $this->assertSame(500, $error->getCode());
    $this->assertSame('internal_error', $error->getType());
    $this->assertNotEmpty($error->getMessage());
    $this->assertStringContainsString('Failed saving', (string) $error->getMessage());
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

    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('getFeaturedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->projectsFeaturedGet('', '', 20, null, '', '', $response_code, $response_headers);

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

    $this->object->projectsGet('en', 'category', '', 20, null, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsIdRecommendationsGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsIdRecommendationsGet('id', 'category', 'en', '', 20, null, '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   * @throws Exception
   */
  public function testProjectsIdRecommendationsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($this->createStub(Project::class));
    $loader->method('getRecommendedProjects')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectsIdRecommendationsGet('id', 'category', 'en', '', 20, null, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectsListResponse::class, $response);
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

    $this->object->projectsSearchGet('query', '', 20, null, '', '', $response_code, $response_headers);

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

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet('', 20, null, '', '', $response_code, $response_headers);

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

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('1');
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->projectsUserGet('', 20, null, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectsListResponse::class, $response);
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

    $request_validator = $this->createStub(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(true);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', '', 20, null, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ProjectsListResponse::class, $response);
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

    $request_validator = $this->createStub(ProjectsRequestValidator::class);
    $request_validator->method('validateUserExists')->willReturn(false);
    $this->facade->method('getRequestValidator')->willReturn($request_validator);

    $response = $this->object->projectsUserIdGet('id', '', 20, null, '', '', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
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

    $user = $this->createStub(User::class);
    $user->method('isVerified')->willReturn(true);
    $processor = $this->createStub(ProjectsApiProcessor::class);
    $processor->method('addProject')->willReturn($this->createStub(Project::class));
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createStub(UploadedFile::class);
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

    $validator = $this->createStub(ProjectsRequestValidator::class);
    $validation_wrapper = $this->createStub(ValidationWrapper::class);
    $validation_wrapper->method('hasError')->willReturn(true);
    $validator->method('validateUploadFile')->willReturn($validation_wrapper);
    $processor = $this->createStub(ProjectsApiProcessor::class);
    $processor->method('addProject')->willReturn($this->createStub(Project::class));
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $user = $this->createStub(User::class);
    $user->method('isVerified')->willReturn(true);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);
    $this->facade->method('getRequestValidator')->willReturn($validator);

    $file = $this->createStub(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, 'en', '', false, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(ErrorResponse::class, $response);
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

    $processor = $this->createStub(ProjectsApiProcessor::class);
    $processor->method('addProject')->willThrowException(new \Exception());
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $user = $this->createStub(User::class);
    $user->method('isVerified')->willReturn(true);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);
    $this->facade->method('getProcessor')->willReturn($processor);

    $file = $this->createStub(UploadedFile::class);
    $response = $this->object->projectsPost('checksum', $file, 'en', '', false, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertInstanceOf(ErrorResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @throws \Exception
   */
  public function testProjectsIdDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->projectsIdDelete('id', $response_code, $response_headers);

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

  public function testCreateProjectCatrobatFileResponseSanitizesSlashesInName(): void
  {
    $tmp = tempnam(sys_get_temp_dir(), 'catrobat_test_');
    $this->assertIsString($tmp);
    file_put_contents($tmp, 'dummy');
    $file = new \Symfony\Component\HttpFoundation\File\File($tmp);

    $response = $this->full_response_manager->createProjectCatrobatFileResponse('abc', $file, 'path/to\project');

    $disposition = $response->headers->get('Content-Disposition');
    $this->assertStringContainsString('path_to_project.catrobat', $disposition);
    $this->assertStringNotContainsString('/', $disposition);

    unlink($tmp);
  }
}
