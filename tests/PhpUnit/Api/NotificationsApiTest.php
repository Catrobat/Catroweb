<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\NotificationsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Notifications\NotificationsApiFacade;
use App\DB\Entity\User\User;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\NotificationsCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(NotificationsApi::class)]
class NotificationsApiTest extends DefaultTestCase
{
  protected MockObject|NotificationsApi $object;

  protected MockObject|NotificationsApiFacade $facade;

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApi::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getAuthenticationToken'])
      ->getMock()
    ;

    $this->facade = $this->createMock(NotificationsApiFacade::class);
    $this->mockProperty(NotificationsApi::class, $this->object, 'facade', $this->facade);
  }

  #[Group('integration')]
  public function testCtor(): void
  {
    $this->object = new NotificationsApi($this->facade);
    $this->assertInstanceOf(NotificationsApi::class, $this->object);
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

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $user = $this->createMock(User::class);
    $user->method('getId')->willReturn('1');
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $response = $this->object->notificationsCountGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(NotificationsCountResponse::class, $response);
  }

  #[Group('unit')]
  public function testNotificationsGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $response = $this->object->notificationsGet('en', 20, 0, '', 'all', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_IMPLEMENTED, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testNotificationsReadPut(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createMock(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createMock(User::class));
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->object->notificationsReadPut($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }
}
