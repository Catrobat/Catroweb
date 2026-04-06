<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Studio\StudioApiFacade;
use App\Api\Services\Studio\StudioApiLoader;
use App\Api\Services\Studio\StudioApiProcessor;
use App\Api\Services\Studio\StudioResponseManager;
use App\Api\StudioApi;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use OpenAPI\Server\Model\StudioAddProjectRequest;
use OpenAPI\Server\Model\StudioCommentCreateRequest;
use OpenAPI\Server\Model\StudioCommentListResponse;
use OpenAPI\Server\Model\StudioCommentResponse;
use OpenAPI\Server\Model\StudioListResponse;
use OpenAPI\Server\Model\StudioMemberListResponse;
use OpenAPI\Server\Model\StudioProjectListResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(StudioApi::class)]
final class StudioApiTest extends TestCase
{
  private StudioApi $api;
  private \PHPUnit\Framework\MockObject\Stub&StudioApiFacade $facade;
  private \PHPUnit\Framework\MockObject\Stub&StudioApiLoader $loader;
  private \PHPUnit\Framework\MockObject\Stub&StudioApiProcessor $processor;
  private \PHPUnit\Framework\MockObject\Stub&StudioResponseManager $response_manager;
  private \PHPUnit\Framework\MockObject\Stub&AuthenticationManager $auth_manager;

  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(StudioApiFacade::class);
    $this->loader = $this->createStub(StudioApiLoader::class);
    $this->processor = $this->createStub(StudioApiProcessor::class);
    $this->response_manager = $this->createStub(StudioResponseManager::class);
    $this->auth_manager = $this->createStub(AuthenticationManager::class);

    $this->facade->method('getLoader')->willReturn($this->loader);
    $this->facade->method('getProcessor')->willReturn($this->processor);
    $this->facade->method('getResponseManager')->willReturn($this->response_manager);
    $this->facade->method('getAuthenticationManager')->willReturn($this->auth_manager);

    $this->api = new StudioApi(
      $this->facade,
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
    );
  }

  #[Group('unit')]
  public function testStudioGetReturnsListResponse(): void
  {
    $response_code = 200;
    $response_headers = [];
    $expected = new StudioListResponse();
    $expected->setData([]);
    $expected->setHasMore(false);

    $this->loader->method('loadStudiosPage')->willReturn(['studios' => [], 'has_more' => false]);
    $this->response_manager->method('createStudioListResponse')->willReturn($expected);

    $result = $this->api->studioGet('en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(StudioListResponse::class, $result);
  }

  #[Group('unit')]
  public function testStudioGetBadCursorReturns400(): void
  {
    $response_code = 200;
    $response_headers = [];

    $result = $this->api->studioGet('en', 20, 'invalid!!!', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testStudioIdGetNotFoundReturns404(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->loader->method('loadVisibleStudio')->willReturn(null);

    $result = $this->api->studioIdGet('nonexistent', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testStudioIdGetPrivateNonMemberReturnsForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $studio = $this->createStub(Studio::class);
    $studio->method('isIsPublic')->willReturn(false);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->loader->method('loadStudioUser')->willReturn(null);

    $result = $this->api->studioIdGet('some-id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testStudioIdMembersGetNotFoundReturns404(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->loader->method('loadVisibleStudio')->willReturn(null);

    $result = $this->api->studioIdMembersGet('nonexistent', 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testStudioIdMembersGetReturnsListResponse(): void
  {
    $response_code = 200;
    $response_headers = [];

    $studio = $this->createStub(Studio::class);
    $studio->method('isIsPublic')->willReturn(true);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->loader->method('loadMembersPage')->willReturn(['members' => [], 'has_more' => false]);
    $this->response_manager->method('createMemberListResponse')
      ->willReturn(new StudioMemberListResponse())
    ;

    $result = $this->api->studioIdMembersGet('some-id', 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(StudioMemberListResponse::class, $result);
  }

  #[Group('unit')]
  public function testJoinStudioUnauthenticatedReturns401(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->auth_manager->method('getAuthenticatedUser')->willReturn(null);

    $this->api->studioIdJoinPost('some-id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  #[Group('unit')]
  public function testJoinStudioAlreadyMemberReturnsConflict(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);

    $studioUser = $this->createStub(StudioUser::class);
    $this->loader->method('loadStudioUser')->willReturn($studioUser);

    $this->api->studioIdJoinPost('some-id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CONFLICT, $response_code);
  }

  #[Group('unit')]
  public function testLeaveStudioAdminReturnsUnprocessable(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);

    $studioUser = $this->createStub(StudioUser::class);
    $studioUser->method('isAdmin')->willReturn(true);
    $this->loader->method('loadStudioUser')->willReturn($studioUser);

    $this->api->studioIdLeaveDelete('some-id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
  }

  #[Group('unit')]
  public function testStudioProjectsGetReturnsListResponse(): void
  {
    $response_code = 200;
    $response_headers = [];

    $studio = $this->createStub(Studio::class);
    $studio->method('isIsPublic')->willReturn(true);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->loader->method('loadProjectsPage')->willReturn(['projects' => [], 'has_more' => false]);
    $this->response_manager->method('createProjectListResponse')
      ->willReturn(new StudioProjectListResponse())
    ;

    $result = $this->api->studioIdProjectsGet('some-id', 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(StudioProjectListResponse::class, $result);
  }

  #[Group('unit')]
  public function testAddProjectNotMemberReturnsForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->loader->method('loadStudioUser')->willReturn(null);

    $request = $this->createStub(StudioAddProjectRequest::class);
    $request->method('getProjectId')->willReturn('project-1');

    $this->api->studioIdProjectsPost('some-id', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
  }

  #[Group('unit')]
  public function testStudioCommentsGetReturnsListResponse(): void
  {
    $response_code = 200;
    $response_headers = [];

    $studio = $this->createStub(Studio::class);
    $studio->method('isIsPublic')->willReturn(true);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->loader->method('loadCommentsPage')->willReturn(['comments' => [], 'has_more' => false]);
    $this->response_manager->method('createCommentListResponse')
      ->willReturn(new StudioCommentListResponse())
    ;

    $result = $this->api->studioIdCommentsGet('some-id', 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(StudioCommentListResponse::class, $result);
  }

  #[Group('unit')]
  public function testPostCommentEmptyMessageReturns400(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $studio->method('isAllowComments')->willReturn(true);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);

    $studioUser = $this->createStub(StudioUser::class);
    $this->loader->method('loadStudioUser')->willReturn($studioUser);

    $request = $this->createStub(StudioCommentCreateRequest::class);
    $request->method('getMessage')->willReturn('   ');

    $result = $this->api->studioIdCommentsPost('some-id', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testPostCommentSuccessReturnsCreated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $studio->method('isAllowComments')->willReturn(true);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);

    $studioUser = $this->createStub(StudioUser::class);
    $this->loader->method('loadStudioUser')->willReturn($studioUser);

    $comment = $this->createStub(UserComment::class);
    $this->processor->method('addComment')->willReturn($comment);

    $commentResponse = new StudioCommentResponse();
    $this->response_manager->method('createCommentResponse')->willReturn($commentResponse);

    $request = $this->createStub(StudioCommentCreateRequest::class);
    $request->method('getMessage')->willReturn('Hello studio!');

    $result = $this->api->studioIdCommentsPost('some-id', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CREATED, $response_code);
    $this->assertInstanceOf(StudioCommentResponse::class, $result);
  }

  #[Group('unit')]
  public function testPostCommentDisabledReturnsForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $studio->method('isAllowComments')->willReturn(false);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);

    $request = $this->createStub(StudioCommentCreateRequest::class);

    $result = $this->api->studioIdCommentsPost('some-id', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testDeleteProjectNotMemberReturnsForbidden(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $studio = $this->createStub(Studio::class);
    $this->loader->method('loadVisibleStudio')->willReturn($studio);
    $this->loader->method('loadStudioUser')->willReturn(null);

    $this->api->studioIdProjectsProjectIdDelete('some-id', 'project-1', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
  }
}
