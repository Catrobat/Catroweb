<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\Api\Services\Reactions\ReactionsApiLoader;
use App\Api\Services\Reactions\ReactionsApiProcessor;
use App\Api\Services\Reactions\ReactionsResponseManager;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\ReactionRequest;
use OpenAPI\Server\Model\ReactionSummaryResponse;
use OpenAPI\Server\Model\ReactionUsersResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
final class ReactionsApiTest extends DefaultTestCase
{
  protected ProjectsApi $object;

  protected Stub|ProjectsApiFacade $facade;

  protected Stub|ReactionsApiFacade $reactions_facade;

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(ProjectsApiFacade::class);
    $this->reactions_facade = $this->createStub(ReactionsApiFacade::class);
    $this->object = new ProjectsApi($this->facade, $this->reactions_facade);
  }

  // ==================== projectIdReactionPost Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPostUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $reaction_request = $this->createStub(ReactionRequest::class);

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPostNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn(null);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $reaction_request = $this->createStub(ReactionRequest::class);

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPostNullType(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $reaction_request = $this->createStub(ReactionRequest::class);
    $reaction_request->method('getType')->willReturn(null);

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPostInvalidType(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $reaction_request = $this->createStub(ReactionRequest::class);
    $reaction_request->method('getType')->willReturn('invalid_type');

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPostConflict(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $processor = $this->createStub(ReactionsApiProcessor::class);
    $processor->method('addReaction')->willReturn(false);
    $this->reactions_facade->method('getProcessor')->willReturn($processor);

    $reaction_request = $this->createStub(ReactionRequest::class);
    $reaction_request->method('getType')->willReturn('thumbs_up');

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CONFLICT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionPost(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $loader->method('getReactionCounts')->willReturn([
      'total' => 1, 'thumbs_up' => 1, 'smile' => 0, 'love' => 0, 'wow' => 0, 'active_types' => ['thumbs_up'],
    ]);
    $loader->method('getUserReactions')->willReturn(['thumbs_up']);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $processor = $this->createStub(ReactionsApiProcessor::class);
    $processor->method('addReaction')->willReturn(true);
    $this->reactions_facade->method('getProcessor')->willReturn($processor);

    $response_manager = $this->createStub(ReactionsResponseManager::class);
    $response_manager->method('createReactionSummaryResponse')
      ->willReturn($this->createStub(ReactionSummaryResponse::class))
    ;
    $this->reactions_facade->method('getResponseManager')->willReturn($response_manager);

    $reaction_request = $this->createStub(ReactionRequest::class);
    $reaction_request->method('getType')->willReturn('thumbs_up');

    $response = $this->object->projectIdReactionPost('id', $reaction_request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CREATED, $response_code);
    $this->assertInstanceOf(ReactionSummaryResponse::class, $response);
  }

  // ==================== projectIdReactionDelete Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionDeleteUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object->projectIdReactionDelete('id', 'thumbs_up', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionDeleteNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn(null);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $this->object->projectIdReactionDelete('id', 'thumbs_up', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionDeleteInvalidType(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $this->object->projectIdReactionDelete('id', 'invalid_type', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionDelete(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $processor = $this->createStub(ReactionsApiProcessor::class);
    $this->reactions_facade->method('getProcessor')->willReturn($processor);

    $this->object->projectIdReactionDelete('id', 'thumbs_up', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NO_CONTENT, $response_code);
  }

  // ==================== projectIdReactionsGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn(null);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdReactionsGet('id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsGetAuthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $loader->method('getReactionCounts')->willReturn([
      'total' => 5, 'thumbs_up' => 2, 'smile' => 1, 'love' => 1, 'wow' => 1, 'active_types' => ['thumbs_up', 'smile', 'love', 'wow'],
    ]);
    $loader->method('getUserReactions')->willReturn(['thumbs_up', 'love']);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(ReactionsResponseManager::class);
    $response_manager->method('createReactionSummaryResponse')
      ->willReturn($this->createStub(ReactionSummaryResponse::class))
    ;
    $this->reactions_facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->projectIdReactionsGet('id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ReactionSummaryResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsGetUnauthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $loader->method('getReactionCounts')->willReturn([
      'total' => 3, 'thumbs_up' => 1, 'smile' => 1, 'love' => 1, 'wow' => 0, 'active_types' => ['thumbs_up', 'smile', 'love'],
    ]);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(ReactionsResponseManager::class);
    $response_manager->method('createReactionSummaryResponse')
      ->willReturn($this->createStub(ReactionSummaryResponse::class))
    ;
    $this->reactions_facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->projectIdReactionsGet('id', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ReactionSummaryResponse::class, $response);
  }

  // ==================== projectIdReactionsUsersGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsUsersGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn(null);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response = $this->object->projectIdReactionsUsersGet('id', 'en', null, 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsUsersGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $loader->method('getReactionUsersPaginated')->willReturn([
      'data' => [],
      'next_cursor' => null,
      'has_more' => false,
    ]);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(ReactionsResponseManager::class);
    $response_manager->method('createReactionUsersResponse')
      ->willReturn($this->createStub(ReactionUsersResponse::class))
    ;
    $this->reactions_facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->projectIdReactionsUsersGet('id', 'en', null, 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ReactionUsersResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testProjectIdReactionsUsersGetWithTypeFilter(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->reactions_facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $project = $this->createStub(Program::class);
    $loader = $this->createStub(ReactionsApiLoader::class);
    $loader->method('findProjectIfVisibleToCurrentUser')->willReturn($project);
    $loader->method('getReactionUsersPaginated')->willReturn([
      'data' => [],
      'next_cursor' => null,
      'has_more' => false,
    ]);
    $this->reactions_facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(ReactionsResponseManager::class);
    $response_manager->method('createReactionUsersResponse')
      ->willReturn($this->createStub(ReactionUsersResponse::class))
    ;
    $this->reactions_facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->projectIdReactionsUsersGet('id', 'en', 'love', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(ReactionUsersResponse::class, $response);
  }
}
