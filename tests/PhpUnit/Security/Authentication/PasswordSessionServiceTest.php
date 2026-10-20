<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use App\Security\Authentication\PasswordSessionService;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @internal
 */
#[CoversClass(PasswordSessionService::class)]
final class PasswordSessionServiceTest extends KernelTestCase
{
  private EntityManagerInterface $entity_manager;
  private PasswordSessionService $service;
  private \App\Security\Authentication\AccountSessionCoordinator $coordinator;
  private User $user;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $this->entity_manager = $entity_manager;
    $service = self::getContainer()->get(PasswordSessionService::class);
    $this->assertInstanceOf(PasswordSessionService::class, $service);
    $this->service = $service;
    $coordinator = self::getContainer()->get(\App\Security\Authentication\AccountSessionCoordinator::class);
    $this->assertInstanceOf(\App\Security\Authentication\AccountSessionCoordinator::class, $coordinator);
    $this->coordinator = $coordinator;
    $this->user = new User();
    $this->user->setUsername('sessions-7430-'.bin2hex(random_bytes(4)));
    $this->user->setEmail($this->user->getUsername().'@localhost.org');
    $this->user->setPassword('old-hash');
    $user_manager = self::getContainer()->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);
    $user_manager->updateUser($this->user);
    foreach (['current', 'other'] as $token) {
      $this->entity_manager->persist(RefreshToken::createForUserWithTtl($this->token($token), $this->user, 3600));
    }
    $this->entity_manager->flush();
  }

  public function testCurrentSessionRotatesPersistedRefreshAndFreshValidAccessTokens(): void
  {
    $request = new Request(cookies: ['REFRESH_TOKEN' => $this->token('current')]);
    $result = $this->service->changePassword($this->user, $request, function (): void {
      $this->user->setPassword('new-hash');
      $this->entity_manager->flush();
    });
    $this->assertNotNull($result);
    $this->assertNotSame($this->token('current'), $result['refresh']);
    $this->assertSame(1, $this->countTokens());
    $this->assertFalse($this->entity_manager->getConnection()->fetchOne('SELECT id FROM refresh_tokens WHERE refresh_token = ?', [$this->token('current')]));
    $this->assertFalse($this->entity_manager->getConnection()->fetchOne('SELECT id FROM refresh_tokens WHERE refresh_token = ?', [$this->token('other')]));
    $jwt = self::getContainer()->get(JWTTokenManagerInterface::class);
    $this->assertInstanceOf(JWTTokenManagerInterface::class, $jwt);
    $payload = $jwt->parse($result['bearer']);
    $this->assertNotEmpty($payload['jti']);
    $cutoff = $this->user->getCredentialsChangedAt();
    $this->assertNotNull($cutoff);
    $this->assertGreaterThanOrEqual($cutoff->getTimestamp(), $payload['iat']);
    $proof = $this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $result['refresh']]);
    $this->assertInstanceOf(RefreshToken::class, $proof);
    $refreshed = $this->coordinator->withVerifiedRefresh($this->user, $proof, fn (): string => $jwt->create($this->user));
    $this->assertSame($this->user->getUserIdentifier(), $jwt->parse($refreshed)['username']);
  }

  public function testMissingProofRevokesAllAndPreservesNoSession(): void
  {
    $result = $this->service->changePassword($this->user, new Request(), function (): void {
      $this->user->setPassword('new-hash');
    });
    $this->assertNull($result);
    $this->assertSame(0, $this->countTokens());
  }

  public function testResetRevokesAllAndConsumesResetProofInTheSameTransaction(): void
  {
    $consumed = false;
    $this->service->resetPassword($this->user, 'reset-hash', static function () use (&$consumed): void {
      $consumed = true;
    });
    $this->assertTrue($consumed);
    $this->assertSame(0, $this->countTokens());
    $this->assertSame('reset-hash', $this->entity_manager->getConnection()->fetchOne('SELECT password FROM fos_user WHERE id = ?', [$this->user->getId()]));
  }

  public function testARefreshAuthenticatedBeforeRevocationCannotIssueAfterward(): void
  {
    $proof = $this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $this->token('current')]);
    $this->assertInstanceOf(RefreshToken::class, $proof);
    $this->service->resetPassword($this->user, 'reset-hash', static function (): void {});
    $issued = false;
    try {
      $this->coordinator->withVerifiedRefresh($this->user, $proof, static function () use (&$issued): void {
        $issued = true;
      });
      $this->fail('A stale authenticated proof must be rejected after revocation');
    } catch (BadCredentialsException) {
      $this->assertFalse($issued);
    }
  }

  #[DataProvider('stalePatchBodies')]
  public function testTheHttpRouteReturns401AndClearsCookiesForAConcurrentCredentialChange(bool $change_password, bool $ban_after_authentication): void
  {
    $this->user->setVerified(true);
    $this->user->setEnabled(true);
    $this->user->setPlainPassword('OldPassword123');
    $user_manager = self::getContainer()->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);
    $user_manager->updateUser($this->user);
    $jwt = self::getContainer()->get(JWTTokenManagerInterface::class);
    $this->assertInstanceOf(JWTTokenManagerInterface::class, $jwt);
    $bearer = $jwt->create($this->user);
    $dispatcher = self::getContainer()->get('event_dispatcher');
    $this->assertInstanceOf(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class, $dispatcher);
    $original_hash = $this->user->getPassword();
    $listener = function (\Symfony\Component\HttpKernel\Event\ControllerEvent $event) use ($ban_after_authentication): void {
      if ('/api/users/me' === $event->getRequest()->getPathInfo()) {
        // Reproduce a concurrent commit after authentication and before business logic.
        if ($ban_after_authentication) {
          $this->assertFalse($this->user->getProfileHidden(), 'the authenticated user still has its pre-ban state');
          $this->entity_manager->getConnection()->executeStatement('UPDATE fos_user SET profile_hidden = 1, credentials_changed_at = UTC_TIMESTAMP() WHERE id = ?', [$this->user->getId()]);
        } else {
          $this->entity_manager->getConnection()->executeStatement('UPDATE fos_user SET password = ? WHERE id = ?', ['concurrent-hash', $this->user->getId()]);
        }
        $event->getRequest()->attributes->set(\App\Security\Authentication\JwtRefresh\RefreshTokenService::REFRESHED_BEARER_COOKIE_ATTRIBUTE, 'stale-auto-refresh');
      }
    };
    $dispatcher->addListener(\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER, $listener);
    $client = self::getContainer()->get('test.client');
    $this->assertInstanceOf(\Symfony\Bundle\FrameworkBundle\KernelBrowser::class, $client);
    $client->disableReboot();
    $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie('REFRESH_TOKEN', $this->token('current')));
    try {
      $body = $change_password
        ? ['current_password' => 'OldPassword123', 'password' => 'NewPassword123']
        : ['username' => $this->user->getUserIdentifier()];
      $client->request('PATCH', '/api/users/me', [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$bearer,
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
      ], json_encode($body, JSON_THROW_ON_ERROR));
      $response = $client->getResponse();
      $this->assertSame(401, $response->getStatusCode());
      $this->assertCount(2, $response->headers->getCookies());
      foreach ($response->headers->getCookies() as $cookie) {
        $this->assertLessThan(time(), $cookie->getExpiresTime());
      }
      $this->assertSame(2, $this->countTokens());
      if ($ban_after_authentication) {
        $this->assertSame($original_hash, $this->entity_manager->getConnection()->fetchOne('SELECT password FROM fos_user WHERE id = ?', [$this->user->getId()]));
        $this->assertTrue($this->user->getProfileHidden());
        $this->assertNotFalse($this->entity_manager->getConnection()->fetchOne('SELECT id FROM refresh_tokens WHERE refresh_token = ?', [$this->token('current')]), 'a still-stored pre-ban proof must never be rotated into a fresh session');
      }
    } finally {
      $dispatcher->removeListener(\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER, $listener);
    }
  }

  /** @return iterable<string, array{bool, bool}> */
  public static function stalePatchBodies(): iterable
  {
    yield 'password change' => [true, false];
    yield 'unchanged username' => [false, false];
    yield 'password change after ban with surviving proof' => [true, true];
    yield 'username update after ban with surviving proof' => [false, true];
  }

  #[DataProvider('suspendedLoginModes')]
  public function testSuspendedPasswordLoginPreservesCookieAppealsAndProfileUpdates(bool $cookie_mode): void
  {
    $this->user->setVerified(true);
    $this->user->setEnabled(true);
    $this->user->setProfileHidden(true);
    $this->user->setPlainPassword('OldPassword123');
    $user_manager = self::getContainer()->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);
    $user_manager->updateUser($this->user);
    $client = self::getContainer()->get('test.client');
    $this->assertInstanceOf(\Symfony\Bundle\FrameworkBundle\KernelBrowser::class, $client);
    $client->disableReboot();
    $headers = ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'];
    if ($cookie_mode) {
      $headers['HTTP_X_AUTH_MODE'] = 'cookie';
    }
    $client->request('POST', '/api/authentication', [], [], $headers, json_encode([
      'username' => $this->user->getUserIdentifier(), 'password' => 'OldPassword123',
    ], JSON_THROW_ON_ERROR));
    if (!$cookie_mode) {
      $this->assertSame(401, $client->getResponse()->getStatusCode());
      $this->assertSame(2, $this->countTokens());

      return;
    }
    $this->assertSame(200, $client->getResponse()->getStatusCode());
    $this->assertNotNull($client->getCookieJar()->get('BEARER'));
    $client->request('POST', '/api/users/'.$this->user->getId().'/appeal', [], [], $headers, '{"reason":"Please review this suspension"}');
    $this->assertSame(201, $client->getResponse()->getStatusCode(), 'cookie-mode password login must still permit appeals');
    $client->request('PATCH', '/api/users/me', [], [], $headers, json_encode(['username' => $this->user->getUserIdentifier()], JSON_THROW_ON_ERROR));
    $this->assertSame(204, $client->getResponse()->getStatusCode(), 'an already suspended actor may update their profile');
    $client->request('PATCH', '/api/users/me', [], [], $headers, '{"current_password":"OldPassword123","password":"NewPassword123"}');
    $this->assertSame(204, $client->getResponse()->getStatusCode(), 'the existing suspended password-update exemption is preserved');
    $this->assertSame(1, $this->countTokens());
    $this->assertSame(1, (int) $this->entity_manager->getConnection()->fetchOne('SELECT profile_hidden FROM fos_user WHERE id = ?', [$this->user->getId()]));
    $proof = $client->getCookieJar()->get('REFRESH_TOKEN');
    $this->assertNotNull($proof);
    $client->request('POST', '/api/authentication/refresh', [], [], $headers, json_encode(['refresh_token' => $proof->getValue()], JSON_THROW_ON_ERROR));
    $this->assertSame(401, $client->getResponse()->getStatusCode(), 'refresh remains blocked for suspended users');
    $this->assertSame(1, $this->countTokens());
  }

  /** @return iterable<string, array{bool}> */
  public static function suspendedLoginModes(): iterable
  {
    yield 'cookie login can appeal and update profile' => [true];
    yield 'ordinary API login stays blocked' => [false];
  }

  public function testResetOnABannedAccountChangesItsPasswordWithoutUnbanningOrIssuingTokens(): void
  {
    $this->user->setProfileHidden(true);
    $this->entity_manager->flush();
    $this->service->resetPassword($this->user, 'reset-while-banned', static function (): void {});
    $this->assertTrue($this->user->getProfileHidden());
    $this->assertSame('reset-while-banned', $this->entity_manager->getConnection()->fetchOne('SELECT password FROM fos_user WHERE id = ?', [$this->user->getId()]));
    $this->assertSame(0, $this->countTokens());
  }

  public function testPasswordFlushAndRevocationRollBackTogetherOnFailure(): void
  {
    $connection = $this->entity_manager->getConnection();
    try {
      $this->service->changePassword($this->user, new Request(), function (): void {
        $this->user->setPassword('failed-hash');
        $this->entity_manager->flush();
        throw new \RuntimeException('Simulated failure');
      });
      $this->fail('Expected simulated failure');
    } catch (\RuntimeException $exception) {
      $this->assertSame('Simulated failure', $exception->getMessage());
    }
    $this->assertSame(2, $this->countTokens());
    $this->assertSame('old-hash', $connection->fetchOne('SELECT password FROM fos_user WHERE id = ?', [$this->user->getId()]));
  }

  public function testRejectedStalePasswordChangeLeavesTheEntityManagerOpen(): void
  {
    $this->entity_manager->createQuery('UPDATE '.User::class.' u SET u.password = :hash WHERE u.id = :id')
      ->setParameters(['hash' => 'concurrent-hash', 'id' => $this->user->getId()])->execute()
    ;
    try {
      $this->service->changePassword($this->user, new Request(), static function (): void {
        throw new \LogicException('A stale update must never run');
      });
      $this->fail('A stale password change must be rejected');
    } catch (BadCredentialsException) {
      $this->assertTrue($this->entity_manager->isOpen());
      $this->assertSame(2, $this->countTokens());
      $this->entity_manager->flush();
    }
  }

  public function testRejectedResetProofLeavesTheEntityManagerOpenWithoutChangingCredentials(): void
  {
    $exception = new \SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException();
    try {
      $this->service->resetPassword($this->user, 'must-not-save', static function () use ($exception): void {
        throw $exception;
      });
      $this->fail('An invalid reset proof must be rejected');
    } catch (\SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException $actual) {
      $this->assertSame($exception, $actual);
      $this->assertTrue($this->entity_manager->isOpen());
      $this->assertSame('old-hash', $this->user->getPassword());
      $this->assertSame(2, $this->countTokens());
      $this->entity_manager->flush();
    }
  }

  public function testBearerOnlyUsernameChangeStillIssuesAUsableReplacementSession(): void
  {
    $old_name = $this->user->getUserIdentifier();
    $result = $this->service->changeUsername($this->user, new Request(), function (): void {
      $this->user->setUsername($this->user->getUserIdentifier().'-renamed');
    });
    $this->assertNotNull($result);
    $this->assertSame(0, $this->entity_manager->getRepository(RefreshToken::class)->count(['username' => $old_name]));
    $this->assertSame(1, $this->countTokens());
    $proof = $this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $result['refresh']]);
    $this->assertInstanceOf(RefreshToken::class, $proof);
    $this->assertTrue($this->coordinator->isCurrentProof($this->user, $proof));
    $jwt = self::getContainer()->get(JWTTokenManagerInterface::class);
    $this->assertInstanceOf(JWTTokenManagerInterface::class, $jwt);
    $this->assertSame($this->user->getUserIdentifier(), $jwt->parse($result['bearer'])['username']);
  }

  public function testRenameRevokesEveryOldNameTokenBeforeThatNameCanBeRegisteredAgain(): void
  {
    $old_name = $this->user->getUserIdentifier();
    $old_current = $this->token('current');
    $old_other = $this->token('other');
    $proof = $this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $old_other]);
    $this->assertInstanceOf(RefreshToken::class, $proof);
    $result = $this->service->changeUsername($this->user, new Request(cookies: ['REFRESH_TOKEN' => $old_current]), function (): void {
      $this->user->setUsername($this->user->getUserIdentifier().'-renamed');
    });
    $this->assertNotNull($result);
    $this->assertSame(1, $this->countTokens());
    $this->assertSame(0, $this->entity_manager->getRepository(RefreshToken::class)->count(['username' => $old_name]));
    foreach ([$old_current, $old_other] as $old_token) {
      $this->assertNull($this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $old_token]));
    }
    $new_account = new User();
    $new_account->setUsername($old_name);
    $new_account->setEmail($old_name.'-new@localhost.org');
    $new_account->setPassword('different-account');
    $this->entity_manager->persist($new_account);
    $this->entity_manager->flush();
    $this->assertFalse($this->coordinator->isCurrentProof($new_account, $proof), 'old-name re-registration must never revive another account session');
    $issued = false;
    try {
      $this->coordinator->withVerifiedRefresh($new_account, $proof, static function () use (&$issued): void {
        $issued = true;
      });
      $this->fail('The old proof must be rejected for the newly registered account');
    } catch (BadCredentialsException) {
      $this->assertFalse($issued);
      $this->assertTrue($this->entity_manager->isOpen());
    }
    $replacement = $this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $result['refresh']]);
    $this->assertInstanceOf(RefreshToken::class, $replacement);
    $jwt = self::getContainer()->get(JWTTokenManagerInterface::class);
    $this->assertInstanceOf(JWTTokenManagerInterface::class, $jwt);
    $refreshed = $this->coordinator->withVerifiedRefresh($this->user, $replacement, fn (): string => $jwt->create($this->user));
    $this->assertSame($this->user->getUserIdentifier(), $jwt->parse($refreshed)['username']);
  }

  private function token(string $suffix): string
  {
    return $this->user->getUserIdentifier().'-'.$suffix;
  }

  private function countTokens(): int
  {
    return (int) $this->entity_manager->getConnection()->fetchOne('SELECT COUNT(*) FROM refresh_tokens WHERE username = ?', [$this->user->getUserIdentifier()]);
  }
}
