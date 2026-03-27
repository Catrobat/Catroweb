<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\NotificationsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Notifications\NotificationsApiFacade;
use App\Api\Services\Notifications\NotificationsApiLoader;
use App\Api\Services\Notifications\NotificationsResponseManager;
use App\DB\Entity\User\User;
use OpenAPI\Server\Model\NotificationListResponse;
use OpenAPI\Server\Model\NotificationsCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(NotificationsApi::class)]
class NotificationsApiTest extends TestCase
{
  protected NotificationsApi $object;

  protected Stub&NotificationsApiFacade $facade;

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(NotificationsApiFacade::class);
    $this->object = new NotificationsApi(
      $this->facade,
      new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage()),
    );
  }

  #[Group('unit')]
  public function testNotificationIdReadPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->notificationIdReadPut(1, 'en', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsCountGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('1');
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->notificationsCountGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(NotificationsCountResponse::class, $response);
  }

  #[Group('unit')]
  public function testNotificationsGetUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->object->notificationsGet('en', 20, null, 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsGetAuthenticated(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(NotificationsApiLoader::class);
    $loader->method('getNotificationsPage')->willReturn([
      'notifications' => [],
      'has_more' => false,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(NotificationsResponseManager::class);
    $response_manager->method('createNotificationListResponse')->willReturn(new NotificationListResponse([
      'data' => [],
      'next_cursor' => null,
      'has_more' => false,
    ]));
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->object->notificationsGet('en', 20, null, 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(NotificationListResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsGetWithValidCursor(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(NotificationsApiLoader::class);
    $loader->method('getNotificationsPage')->willReturn([
      'notifications' => [],
      'has_more' => false,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(NotificationsResponseManager::class);
    $response_manager->method('createNotificationListResponse')->willReturn(new NotificationListResponse());
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $cursor = base64_encode('42');
    $response = $this->object->notificationsGet('en', 20, $cursor, 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(NotificationListResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsGetWithInvalidCursorNotBase64(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->notificationsGet('en', 20, '!!!invalid!!!', 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsGetWithInvalidCursorNonNumeric(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $cursor = base64_encode('not-a-number');
    $response = $this->object->notificationsGet('en', 20, $cursor, 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsGetLimitNormalization(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(NotificationsApiLoader::class);
    $loader->method('getNotificationsPage')->willReturn([
      'notifications' => [],
      'has_more' => false,
    ]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(NotificationsResponseManager::class);
    $response_manager->method('createNotificationListResponse')->willReturn(new NotificationListResponse());
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    // Test with 0 limit (should normalize to default 20)
    $response = $this->object->notificationsGet('en', 0, null, 'all', $response_code, $response_headers);
    $this->assertEquals(Response::HTTP_OK, $response_code);

    // Test with negative limit (should normalize to default 20)
    $response = $this->object->notificationsGet('en', -5, null, 'all', $response_code, $response_headers);
    $this->assertEquals(Response::HTTP_OK, $response_code);

    // Test with excessive limit (should cap at 50)
    $this->object->notificationsGet('en', 999, null, 'all', $response_code, $response_headers);
    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsReadPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object->notificationsReadPut($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }
}
