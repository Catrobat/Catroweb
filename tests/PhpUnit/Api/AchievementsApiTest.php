<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\AchievementsApi;
use App\Api\Services\Achievements\AchievementsApiFacade;
use App\Api\Services\Achievements\AchievementsApiLoader;
use App\Api\Services\Achievements\AchievementsApiProcessor;
use App\Api\Services\Achievements\AchievementsResponseManager;
use App\Api\Services\AuthenticationManager;
use App\DB\Entity\User\User;
use App\User\UserManager;
use OpenAPI\Server\Model\AchievementResponse;
use OpenAPI\Server\Model\AchievementsCountResponse;
use OpenAPI\Server\Model\AchievementsListResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(AchievementsApi::class)]
class AchievementsApiTest extends TestCase
{
  protected AchievementsApi $object;

  protected Stub&AchievementsApiFacade $facade;

  protected Stub&UserManager $user_manager;

  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(AchievementsApiFacade::class);
    $this->user_manager = $this->createStub(UserManager::class);
    $this->object = new AchievementsApi(
      $this->facade,
      $this->user_manager,
      $this->createNoLimitRateLimiterFactory('phpunit_achievements_burst'),
    );
  }

  #[Group('unit')]
  public function testAchievementsGetUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->object->achievementsGet('en', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  #[Group('unit')]
  public function testAchievementsGetAuthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(AchievementsApiLoader::class);
    $loader->method('getAchievementsPageData')->willReturn([
      'unlocked' => [],
      'locked' => [],
      'most_recent' => null,
      'total_count' => 0,
      'unlocked_count' => 0,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(AchievementsResponseManager::class);
    $response_manager->method('createAchievementsListResponse')->willReturn(new AchievementsListResponse([
      'unlocked' => [],
      'locked' => [],
      'most_recent' => null,
      'most_recent_unlocked_at' => null,
      'show_animation' => false,
      'total_count' => 0,
      'unlocked_count' => 0,
    ]));
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->achievementsGet('en', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(AchievementsListResponse::class, $response);
  }

  #[Group('unit')]
  public function testAchievementsCountGetUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->object->achievementsCountGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  #[Group('unit')]
  public function testAchievementsCountGetAuthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(AchievementsApiLoader::class);
    $loader->method('getUnseenCount')->willReturn(3);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(AchievementsResponseManager::class);
    $response_manager->method('createAchievementsCountResponse')->willReturn(new AchievementsCountResponse([
      'count' => 3,
    ]));
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->achievementsCountGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(AchievementsCountResponse::class, $response);
    $this->assertEquals(3, $response->getCount());
  }

  #[Group('unit')]
  public function testAchievementsReadPutUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->achievementsReadPut($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  #[Group('unit')]
  public function testAchievementsReadPutAuthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $processor = $this->createStub(AchievementsApiProcessor::class);
    $this->facade->method('getProcessor')->willReturn($processor);

    $this->object->achievementsReadPut($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }

  #[Group('unit')]
  public function testUserIdAchievementsGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->user_manager->method('find')->willReturn(null);

    $response = $this->object->userIdAchievementsGet('non-existent-id', 'en', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  #[Group('unit')]
  public function testUserIdAchievementsGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $this->user_manager->method('find')->willReturn($user);

    $loader = $this->createStub(AchievementsApiLoader::class);
    $loader->method('getUnlockedAchievements')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $achievement_response = new AchievementResponse([
      'id' => 1,
      'internal_title' => 'test',
      'title' => 'Test',
      'description' => 'Test desc',
      'badge_svg_path' => '/images/achievements/badge.svg',
      'badge_locked_svg_path' => '/images/achievements/badge_locked.svg',
      'banner_svg_path' => '/images/achievements/banner.svg',
      'banner_color' => '#000',
    ]);

    $response_manager = $this->createStub(AchievementsResponseManager::class);
    $response_manager->method('createAchievementResponseList')->willReturn([$achievement_response]);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->userIdAchievementsGet('valid-id', 'en', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertIsArray($response);
    $this->assertCount(1, $response);
    $this->assertInstanceOf(AchievementResponse::class, $response[0]);
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
}
