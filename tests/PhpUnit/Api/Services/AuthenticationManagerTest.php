<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services;

use App\Api\Services\AuthenticationManager;
use App\User\UserManager;
use App\Utils\RequestHelper;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @internal
 */
#[CoversClass(AuthenticationManager::class)]
final class AuthenticationManagerTest extends TestCase
{
  #[Group('unit')]
  public function testCreatedRefreshTokenIsSavedBeforeReturningItsOriginalValue(): void
  {
    $user = new \App\DB\Entity\User\User();
    $user->setUsername('profile-user');
    $token = \App\DB\Entity\RefreshToken::createForUserWithTtl('new-profile-session', $user, 3600);
    $generator = $this->createMock(RefreshTokenGeneratorInterface::class);
    $generator->expects($this->once())->method('createForUserWithTtl')->with($user, 3600)->willReturn($token);
    $refresh_manager = $this->createMock(RefreshTokenManagerInterface::class);
    $refresh_manager->expects($this->once())->method('save')->with($token)
      ->willReturnCallback(static function (RefreshTokenInterface $stored): void {
        // Managers may replace the in-memory value with a hash on save.
        $stored->setRefreshToken('stored-hash');
      })
    ;
    $manager = new AuthenticationManager(
      $this->createStub(TokenStorageInterface::class),
      $this->createStub(JWTTokenManagerInterface::class),
      $this->createStub(UserManager::class),
      $generator,
      $refresh_manager,
      $this->createStub(RequestHelper::class),
      3600,
    );

    $this->assertSame('new-profile-session', $manager->createRefreshTokenByUser($user));
  }

  #[Group('unit')]
  public function testDeleteRefreshTokenFallsBackToRefreshCookie(): void
  {
    $refresh_manager = $this->createMock(RefreshTokenManagerInterface::class);
    $refresh_token = $this->createStub(RefreshTokenInterface::class);

    $request = Request::create('/api/authentication', 'DELETE');
    $request->cookies->set('REFRESH_TOKEN', 'refresh-cookie');

    $request_helper = $this->createMock(RequestHelper::class);
    $request_helper
      ->expects($this->once())
      ->method('getCurrentRequest')
      ->willReturn($request)
    ;

    $refresh_manager
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        ['placeholder-header', null],
        ['refresh-cookie', $refresh_token],
      ])
    ;
    $refresh_manager
      ->expects($this->once())
      ->method('delete')
      ->with($refresh_token)
    ;

    $manager = new AuthenticationManager(
      $this->createStub(TokenStorageInterface::class),
      $this->createStub(JWTTokenManagerInterface::class),
      $this->createStub(UserManager::class),
      $this->createStub(RefreshTokenGeneratorInterface::class),
      $refresh_manager,
      $request_helper,
      3600,
    );

    $this->assertTrue($manager->deleteRefreshToken('placeholder-header'));
  }
}
