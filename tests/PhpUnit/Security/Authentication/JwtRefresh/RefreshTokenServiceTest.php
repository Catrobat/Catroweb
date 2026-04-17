<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication\JwtRefresh;

use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use App\User\UserManager;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(RefreshTokenService::class)]
final class RefreshTokenServiceTest extends TestCase
{
  private RefreshTokenManagerInterface&MockObject $refresh_manager;
  private RefreshTokenGeneratorInterface $refresh_token_generator;
  private UserManager&MockObject $user_manager;
  private JWTTokenManagerInterface&MockObject $jwt_manager;
  private CookieService $cookie_service;
  private RefreshTokenService $service;

  #[\Override]
  protected function setUp(): void
  {
    unset($_COOKIE['BEARER'], $_COOKIE['REFRESH_TOKEN']);

    $this->refresh_manager = $this->createMock(RefreshTokenManagerInterface::class);
    $this->refresh_token_generator = $this->createStub(RefreshTokenGeneratorInterface::class);
    $this->user_manager = $this->createMock(UserManager::class);
    $this->jwt_manager = $this->createMock(JWTTokenManagerInterface::class);
    $this->cookie_service = $this->createStub(CookieService::class);

    $this->service = new RefreshTokenService(
      2592000,
      $this->refresh_manager,
      $this->refresh_token_generator,
      $this->user_manager,
      $this->jwt_manager,
      $this->cookie_service,
    );
  }

  #[\Override]
  protected function tearDown(): void
  {
    unset($_COOKIE['BEARER'], $_COOKIE['REFRESH_TOKEN']);
  }

  #[Group('unit')]
  public function testRefreshRequestAuthenticationCreatesNewBearerFromRefreshCookie(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->cookies->set('REFRESH_TOKEN', 'refresh-cookie');

    $refresh_token = $this->createStub(RefreshTokenInterface::class);
    $refresh_token->method('isValid')->willReturn(true);
    $refresh_token->method('getUsername')->willReturn('cookie-user');
    $user = $this->createStub(User::class);

    $this->refresh_manager
      ->expects($this->once())
      ->method('get')
      ->with('refresh-cookie')
      ->willReturn($refresh_token)
    ;
    $this->user_manager
      ->expects($this->once())
      ->method('findUserByUsername')
      ->with('cookie-user')
      ->willReturn($user)
    ;
    $this->jwt_manager
      ->expects($this->once())
      ->method('create')
      ->with($user)
      ->willReturn('new-bearer-token')
    ;

    $this->service->refreshRequestAuthentication($request);

    $this->assertSame('new-bearer-token', $request->cookies->get('BEARER'));
    $this->assertSame('new-bearer-token', $_COOKIE['BEARER']);
    $this->assertSame('Bearer new-bearer-token', $request->headers->get('Authorization'));
    $this->assertSame(
      'new-bearer-token',
      $request->attributes->get(RefreshTokenService::REFRESHED_BEARER_COOKIE_ATTRIBUTE),
    );
  }

  #[Group('unit')]
  public function testRefreshRequestAuthenticationSkipsBearerParsingWithoutRefreshCookie(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->cookies->set('BEARER', 'existing-token');

    $this->user_manager
      ->expects($this->never())
      ->method('findUserByUsername')
    ;
    $this->jwt_manager
      ->expects($this->never())
      ->method('parse')
    ;
    $this->refresh_manager
      ->expects($this->never())
      ->method('get')
    ;

    $this->service->refreshRequestAuthentication($request);
  }

  #[Group('unit')]
  public function testRefreshRequestAuthenticationSkipsCookieRefreshWhenAuthorizationHeaderExists(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->headers->set('Authorization', 'Bearer existing-header-token');
    $request->cookies->set('BEARER', 'existing-cookie-token');
    $request->cookies->set('REFRESH_TOKEN', 'refresh-cookie');

    $this->user_manager
      ->expects($this->never())
      ->method('findUserByUsername')
    ;
    $this->jwt_manager
      ->expects($this->never())
      ->method('parse')
    ;
    $this->refresh_manager
      ->expects($this->never())
      ->method('get')
    ;

    $this->service->refreshRequestAuthentication($request);
  }

  #[Group('unit')]
  public function testRefreshRequestAuthenticationBlocksSuspendedUser(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->cookies->set('REFRESH_TOKEN', 'refresh-cookie');
    $_COOKIE['REFRESH_TOKEN'] = 'refresh-cookie';

    $refresh_token = $this->createStub(RefreshTokenInterface::class);
    $refresh_token->method('isValid')->willReturn(true);
    $refresh_token->method('getUsername')->willReturn('suspended-user');

    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->refresh_manager
      ->expects($this->once())
      ->method('get')
      ->with('refresh-cookie')
      ->willReturn($refresh_token)
    ;
    $this->user_manager
      ->expects($this->once())
      ->method('findUserByUsername')
      ->with('suspended-user')
      ->willReturn($user)
    ;
    $this->jwt_manager
      ->expects($this->never())
      ->method('create')
    ;
    $this->refresh_manager
      ->expects($this->once())
      ->method('delete')
      ->with($refresh_token)
    ;

    $this->service->refreshRequestAuthentication($request);

    $this->assertTrue(
      $request->attributes->get(RefreshTokenService::CLEAR_AUTHENTICATION_COOKIES_ATTRIBUTE)
    );
    $this->assertFalse($request->cookies->has('BEARER'));
    $this->assertFalse($request->cookies->has('REFRESH_TOKEN'));
    $this->assertArrayNotHasKey('REFRESH_TOKEN', $_COOKIE);
  }

  #[Group('unit')]
  public function testRefreshRequestAuthenticationClearsInvalidAuthCookies(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->cookies->set('BEARER', 'expired-token');
    $request->cookies->set('REFRESH_TOKEN', 'invalid-refresh-cookie');
    $_COOKIE['BEARER'] = 'expired-token';
    $_COOKIE['REFRESH_TOKEN'] = 'invalid-refresh-cookie';

    $this->jwt_manager
      ->expects($this->once())
      ->method('parse')
      ->with('expired-token')
      ->willThrowException(
        new JWTDecodeFailureException(
          JWTDecodeFailureException::EXPIRED_TOKEN,
          'Expired JWT Token',
        )
      )
    ;
    $this->user_manager
      ->expects($this->never())
      ->method('findUserByUsername')
    ;
    $this->refresh_manager
      ->expects($this->once())
      ->method('get')
      ->with('invalid-refresh-cookie')
      ->willReturn(null)
    ;

    $this->service->refreshRequestAuthentication($request);

    $this->assertTrue(
      $request->attributes->get(RefreshTokenService::CLEAR_AUTHENTICATION_COOKIES_ATTRIBUTE)
    );
    $this->assertFalse($request->cookies->has('BEARER'));
    $this->assertFalse($request->cookies->has('REFRESH_TOKEN'));
    $this->assertArrayNotHasKey('BEARER', $_COOKIE);
    $this->assertArrayNotHasKey('REFRESH_TOKEN', $_COOKIE);
  }
}
