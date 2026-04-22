<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Translation\TranslationApiFacade;
use App\Api\Services\Translation\TranslationApiLoader;
use App\Api\Services\Translation\TranslationApiProcessor;
use App\Api\Services\Translation\TranslationResponseManager;
use App\Api\TranslationApi;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Translation\TranslationResult;
use OpenAPI\Server\Model\ProjectCustomTranslationSaveRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(TranslationApi::class)]
final class TranslationApiTest extends TestCase
{
  /**
   * @throws Exception
   */
  private function buildApi(
    ?TranslationApiFacade $facade = null,
    ?ProjectManager $project_manager = null,
    ?RequestStack $request_stack = null,
  ): TranslationApi {
    return new TranslationApi(
      $facade ?? $this->createDefaultFacade(),
      $project_manager ?? $this->createStub(ProjectManager::class),
      $request_stack ?? new RequestStack(),
    );
  }

  /**
   * @throws Exception
   */
  private function createDefaultFacade(): Stub&TranslationApiFacade
  {
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($this->createStub(TranslationApiLoader::class));
    $facade->method('getProcessor')->willReturn($this->createStub(TranslationApiProcessor::class));
    $facade->method('getResponseManager')->willReturn($this->createStub(TranslationResponseManager::class));
    $facade->method('getAuthenticationManager')->willReturn($this->createStub(AuthenticationManager::class));

    return $facade;
  }

  private function createProjectStub(string $id = '1', string $name = 'Test', string $description = 'Desc', string $credits = ''): Stub&Project
  {
    $project = $this->createStub(Project::class);
    $project->method('getId')->willReturn($id);
    $project->method('getName')->willReturn($name);
    $project->method('getDescription')->willReturn($description);
    $project->method('getCredits')->willReturn($credits);

    return $project;
  }

  // --- projectsIdTranslationGet ---

  #[Group('unit')]
  public function testTranslationGetProjectNotFound(): void
  {
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationGet('nonexistent', 'fr', 'en', null, $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testTranslationGetSameSourceAndTarget(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $api = $this->buildApi(project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationGet('1', 'fr', 'en', 'fr', $code, $headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testTranslationGetProviderUnavailable(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $loader = $this->createStub(TranslationApiLoader::class);
    $loader->method('translateProject')->willReturn(null);

    $facade = $this->createDefaultFacade();
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);

    $request_stack = new RequestStack();
    $request_stack->push(new Request());

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager, request_stack: $request_stack);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationGet('1', 'fr', 'en', null, $code, $headers);

    $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testTranslationGetProviderReturnsNullTitle(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $loader = $this->createStub(TranslationApiLoader::class);
    $loader->method('translateProject')->willReturn([null, null, null]);

    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);

    $request_stack = new RequestStack();
    $request_stack->push(new Request());

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager, request_stack: $request_stack);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationGet('1', 'fr', 'en', null, $code, $headers);

    $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testTranslationGetSuccess(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $title_result = new TranslationResult();
    $title_result->translation = 'Translated';
    $title_result->detected_source_language = 'en';
    $title_result->provider = 'google';
    $loader = $this->createStub(TranslationApiLoader::class);
    $loader->method('translateProject')->willReturn([$title_result, null, null]);

    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);
    $facade->method('getResponseManager')->willReturn($this->createStub(TranslationResponseManager::class));

    $request_stack = new RequestStack();
    $request_stack->push(new Request());

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager, request_stack: $request_stack);

    $code = 200;
    $headers = [];
    $api->projectsIdTranslationGet('1', 'fr', 'en', null, $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
    $this->assertArrayHasKey('ETag', $headers);
  }

  #[Group('unit')]
  public function testTranslationGetEtagNotModified(): void
  {
    $project = $this->createProjectStub('1', 'Test', 'Desc', '');
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $etag_value = md5('TestDesc').'fr';

    $request = new Request();
    $request->headers->set('If-None-Match', '"'.$etag_value.'"');
    $request_stack = new RequestStack();
    $request_stack->push($request);

    $api = $this->buildApi(project_manager: $project_manager, request_stack: $request_stack);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationGet('1', 'fr', 'en', null, $code, $headers);

    $this->assertSame(Response::HTTP_NOT_MODIFIED, $code);
    $this->assertNull($result);
  }

  // --- projectsIdTranslationFieldLanguageGet ---

  #[Group('unit')]
  public function testCustomTranslationGetProjectNotFound(): void
  {
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationFieldLanguageGet('1', 'name', 'fr', 'en', $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCustomTranslationGetNotFound(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $loader = $this->createStub(TranslationApiLoader::class);
    $loader->method('getCustomTranslation')->willReturn(null);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationFieldLanguageGet('1', 'name', 'fr', 'en', $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
    $this->assertNull($result);
  }

  // --- projectsIdTranslationFieldLanguagePut ---

  #[Group('unit')]
  public function testCustomTranslationPutUnauthorized(): void
  {
    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $request = new ProjectCustomTranslationSaveRequest(['text' => 'hello']);
    $api->projectsIdTranslationFieldLanguagePut('1', 'name', 'fr', $request, 'en', $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
  }

  #[Group('unit')]
  public function testCustomTranslationPutNotOwnProject(): void
  {
    $user = $this->createStub(User::class);
    $other_user = $this->createStub(User::class);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $project = $this->createProjectStub();
    $project->method('getUser')->willReturn($other_user);
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('find')->willReturn($project);

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $request = new ProjectCustomTranslationSaveRequest(['text' => 'hello']);
    $api->projectsIdTranslationFieldLanguagePut('1', 'name', 'fr', $request, 'en', $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
  }

  #[Group('unit')]
  public function testCustomTranslationPutEmptyText(): void
  {
    $user = $this->createStub(User::class);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $project = $this->createProjectStub();
    $project->method('getUser')->willReturn($user);
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('find')->willReturn($project);

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $request = new ProjectCustomTranslationSaveRequest(['text' => '   ']);
    $api->projectsIdTranslationFieldLanguagePut('1', 'name', 'fr', $request, 'en', $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testCustomTranslationPutInvalidField(): void
  {
    $user = $this->createStub(User::class);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $processor = $this->createStub(TranslationApiProcessor::class);
    $processor->method('saveCustomTranslation')->willThrowException(new \InvalidArgumentException());

    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);
    $facade->method('getProcessor')->willReturn($processor);

    $project = $this->createProjectStub();
    $project->method('getUser')->willReturn($user);
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('find')->willReturn($project);

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $request = new ProjectCustomTranslationSaveRequest(['text' => 'hello']);
    $api->projectsIdTranslationFieldLanguagePut('1', 'invalid', 'fr', $request, 'en', $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  // --- projectsIdTranslationFieldLanguageDelete ---

  #[Group('unit')]
  public function testCustomTranslationDeleteUnauthorized(): void
  {
    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->projectsIdTranslationFieldLanguageDelete('1', 'name', 'fr', 'en', $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
  }

  // --- projectsIdTranslationLanguagesGet ---

  #[Group('unit')]
  public function testLanguagesGetProjectNotFound(): void
  {
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationLanguagesGet('1', 'en', $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testLanguagesGetSuccess(): void
  {
    $project = $this->createProjectStub();
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $loader = $this->createStub(TranslationApiLoader::class);
    $loader->method('listDefinedLanguages')->willReturn(['fr', 'de']);
    $facade = $this->createStub(TranslationApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);

    $api = $this->buildApi(facade: $facade, project_manager: $project_manager);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdTranslationLanguagesGet('1', 'en', $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
    $this->assertSame(['fr', 'de'], $result);
  }
}
