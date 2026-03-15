<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\FollowersApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Followers\FollowersApiFacade;
use App\Api\Services\Followers\FollowersApiLoader;
use App\Api\Services\Followers\FollowersApiProcessor;
use App\Api\Services\Followers\FollowersResponseManager;
use App\Api\Services\User\UserApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use OpenAPI\Server\Model\FollowersListResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(FollowersApi::class)]
final class FollowersApiTest extends TestCase
{
  private FollowersApi $object;
  private \PHPUnit\Framework\MockObject\Stub&FollowersApiFacade $facade;
  private \PHPUnit\Framework\MockObject\Stub&UserManager $user_manager;
  private \PHPUnit\Framework\MockObject\Stub&UserApiLoader $user_api_loader;

  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(FollowersApiFacade::class);
    $this->user_manager = $this->createStub(UserManager::class);
    $this->user_api_loader = $this->createStub(UserApiLoader::class);
    $this->user_api_loader->method('canAccessProfile')->willReturn(true);
    $this->object = new FollowersApi(
      $this->facade,
      $this->user_manager,
      $this->user_api_loader,
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
    );
  }

  #[Group('unit')]
  public function testFollowersGetUserNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->user_manager->method('find')->willReturn(null);

    $result = $this->object->userIdFollowersGet('nonexistent', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testFollowersGetHiddenProfileReturns404(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->user_manager->method('find')->willReturn($user);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $user_api_loader = $this->createStub(UserApiLoader::class);
    $user_api_loader->method('canAccessProfile')->willReturn(false);

    $this->object = new FollowersApi(
      $this->facade,
      $this->user_manager,
      $user_api_loader,
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
    );

    $result = $this->object->userIdFollowersGet('hidden-user', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testFollowersGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->user_manager->method('find')->willReturn($user);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $loader = $this->createStub(FollowersApiLoader::class);
    $loader->method('getFollowers')->willReturn([
      'users' => [],
      'total_followers' => 0,
      'total_following' => 0,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $expected_response = new FollowersListResponse([
      'data' => [],
      'total_followers' => 0,
      'total_following' => 0,
    ]);
    $response_manager = $this->createStub(FollowersResponseManager::class);
    $response_manager->method('createFollowersListResponse')->willReturn($expected_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $result = $this->object->userIdFollowersGet('user-1', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(FollowersListResponse::class, $result);
  }

  #[Group('unit')]
  public function testFollowingGetUserNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->user_manager->method('find')->willReturn(null);

    $result = $this->object->userIdFollowingGet('nonexistent', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testFollowingGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->user_manager->method('find')->willReturn($user);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $loader = $this->createStub(FollowersApiLoader::class);
    $loader->method('getFollowing')->willReturn([
      'users' => [],
      'total_followers' => 0,
      'total_following' => 0,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $expected_response = new FollowersListResponse([
      'data' => [],
      'total_followers' => 0,
      'total_following' => 0,
    ]);
    $response_manager = $this->createStub(FollowersResponseManager::class);
    $response_manager->method('createFollowersListResponse')->willReturn($expected_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $result = $this->object->userIdFollowingGet('user-1', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(FollowersListResponse::class, $result);
  }

  #[Group('unit')]
  public function testFollowPostUnauthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->object->userIdFollowPost('target-user', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  #[Group('unit')]
  public function testFollowPostSelfFollow(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->object->userIdFollowPost('user-1', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
  }

  #[Group('unit')]
  public function testFollowPostTargetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->user_manager->method('find')->willReturn(null);

    $this->object->userIdFollowPost('nonexistent', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  #[Group('unit')]
  public function testFollowPostAlreadyFollowing(): void
  {
    $response_code = 200;
    $response_headers = [];

    $target_user = $this->createStub(User::class);

    $following_collection = new ArrayCollection([$target_user]);

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');
    $user->method('getFollowing')->willReturn($following_collection);

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->user_manager->method('find')->willReturn($target_user);

    $this->object->userIdFollowPost('user-2', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
  }

  #[Group('unit')]
  public function testFollowPostSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $target_user = $this->createStub(User::class);

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');
    $user->method('getFollowing')->willReturn(new ArrayCollection());

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->user_manager->method('find')->willReturn($target_user);

    $processor = $this->createStub(FollowersApiProcessor::class);
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->object->userIdFollowPost('user-2', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
  }

  #[Group('unit')]
  public function testUnfollowDeleteUnauthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->object->userIdUnfollowDelete('target-user', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  #[Group('unit')]
  public function testUnfollowDeleteSelfUnfollow(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->object->userIdUnfollowDelete('user-1', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
  }

  #[Group('unit')]
  public function testUnfollowDeleteTargetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->user_manager->method('find')->willReturn(null);

    $this->object->userIdUnfollowDelete('nonexistent', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  #[Group('unit')]
  public function testUnfollowDeleteSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $target_user = $this->createStub(User::class);

    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($auth_manager);

    $this->user_manager->method('find')->willReturn($target_user);

    $processor = $this->createStub(FollowersApiProcessor::class);
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->object->userIdUnfollowDelete('user-2', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NO_CONTENT, $response_code);
  }
}
