<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use App\Security\Authentication\AccountSessionCoordinator;
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
#[CoversClass(AccountSessionCoordinator::class)]
final class AccountSessionCoordinatorTest extends KernelTestCase
{
  private EntityManagerInterface $entity_manager;
  private AccountSessionCoordinator $service;
  private User $user;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $this->entity_manager = $entity_manager;
    $service = self::getContainer()->get(AccountSessionCoordinator::class);
    $this->assertInstanceOf(AccountSessionCoordinator::class, $service);
    $this->service = $service;
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

  public function testLoginAuthenticatedBeforeAConcurrentPasswordChangeCannotIssueAfterward(): void
  {
    // A separate transaction changes persistence while the authenticated User still holds the old hash.
    $this->entity_manager->getConnection()->executeStatement('UPDATE fos_user SET password = ? WHERE id = ?', ['concurrent-hash', $this->user->getId()]);
    $issued = false;
    try {
      $this->service->withVerifiedLogin($this->user, static function () use (&$issued): void {
        $issued = true;
      });
      $this->fail('An authentication against a stale password must not issue tokens');
    } catch (BadCredentialsException) {
      $this->assertFalse($issued);
      $this->assertTrue($this->entity_manager->isOpen());
    }
  }

  #[DataProvider('usernameChanges')]
  public function testAStaleAuthenticatedUsernamePatchCannotUpdateOrIssueTokens(bool $same_username): void
  {
    $original_username = $this->user->getUserIdentifier();
    $storage = self::getContainer()->get(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class);
    $this->assertInstanceOf(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class, $storage);
    $storage->setToken(new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($this->user, 'api', ['ROLE_USER']));
    // The request authenticated against old-hash before another transaction changed the password.
    $this->entity_manager->getConnection()->executeStatement('UPDATE fos_user SET password = ? WHERE id = ?', ['concurrent-hash', $this->user->getId()]);
    $request = new \OpenAPI\Server\Model\UpdateUserRequest();
    $request->setUsername($same_username ? $original_username : $original_username.'-renamed');
    $api = self::getContainer()->get(\App\Api\UsersApi::class);
    $this->assertInstanceOf(\App\Api\UsersApi::class, $api);
    $status = 0;
    $headers = [];
    $http_request = new Request();
    $http_request->attributes->set(\App\Security\Authentication\JwtRefresh\RefreshTokenService::REFRESHED_BEARER_COOKIE_ATTRIBUTE, 'stale-auto-refresh');
    $stack = self::getContainer()->get(\Symfony\Component\HttpFoundation\RequestStack::class);
    $this->assertInstanceOf(\Symfony\Component\HttpFoundation\RequestStack::class, $stack);
    $stack->push($http_request);
    $api->usersMePatch($request, 'en', $status, $headers);
    $this->assertSame(401, $status);
    $this->assertSame(2, $this->countTokens());
    $this->assertFalse($http_request->attributes->has(\App\Security\Authentication\JwtRefresh\RefreshTokenService::REFRESHED_BEARER_COOKIE_ATTRIBUTE));
    $this->assertCount(2, $headers['Set-Cookie']);
    foreach ($headers['Set-Cookie'] as $cookie) {
      $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Cookie::class, $cookie);
      $this->assertLessThan(time(), $cookie->getExpiresTime());
    }
    $this->assertSame($original_username, $this->entity_manager->getConnection()->fetchOne('SELECT username FROM fos_user WHERE id = ?', [$this->user->getId()]));
    $stack->pop();
  }

  /** @return iterable<string, array{bool}> */
  public static function usernameChanges(): iterable
  {
    yield 'unchanged username still requests replacement cookies' => [true];
    yield 'renamed account' => [false];
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

  public function testRejectedStaleFormLoginClearsTheInstalledMainAuthentication(): void
  {
    $storage = self::getContainer()->get(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class);
    $this->assertInstanceOf(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class, $storage);
    $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($this->user, 'main', ['ROLE_USER']);
    // Symfony installs the token before calling the success handler.
    $storage->setToken($token);
    $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
    $session->set('_security_main', serialize($token));
    $session->set('unrelated-preference', 'keep');
    $request = Request::create('/app/login_check', 'POST');
    $request->setSession($session);
    $handler = self::getContainer()->get(\App\Security\Authentication\FormLoginSuccessHandler::class);
    $this->assertInstanceOf(\App\Security\Authentication\FormLoginSuccessHandler::class, $handler);
    $this->entity_manager->getConnection()->executeStatement('UPDATE fos_user SET password = ? WHERE id = ?', ['concurrent-hash', $this->user->getId()]);
    try {
      $handler->onAuthenticationSuccess($request, $token);
      $this->fail('A stale form login must be rejected');
    } catch (BadCredentialsException) {
      $this->assertSame($this->user, $token->getUser());
      $this->assertSame('concurrent-hash', $this->user->getPassword(), 'the same installed User was refreshed before rejection');
      $this->assertNull($storage->getToken());
      $this->assertFalse($session->has('_security_main'));
      $request->attributes->set('_security_firewall_run', '_security_main');
      $context_listener = new \Symfony\Component\Security\Http\Firewall\ContextListener($storage, [], 'main');
      $kernel = self::$kernel;
      $this->assertNotNull($kernel);
      $context_listener->onKernelResponse(new \Symfony\Component\HttpKernel\Event\ResponseEvent($kernel, $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST, new \Symfony\Component\HttpFoundation\Response()));
      $this->assertFalse($session->has('_security_main'), 'the response listener cannot persist the rejected token again');
      $this->assertSame('keep', $session->get('unrelated-preference'));
      $this->assertSame(2, $this->countTokens(), 'rejecting this login must not revoke other sessions');
    }
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
  }

  /** @return iterable<string, array{bool}> */
  public static function suspendedLoginModes(): iterable
  {
    yield 'cookie login can appeal and update profile' => [true];
    yield 'ordinary API login stays blocked' => [false];
  }

  public function testHttpAccountUpdateRejectsAnAuthenticationFromBeforeAConcurrentRename(): void
  {
    $this->user->setVerified(true);
    $this->user->setEnabled(true);
    $this->entity_manager->flush();
    $old_name = $this->user->getUserIdentifier();
    $new_name = $old_name.'-concurrent';
    $concurrent_proof = $old_name.'-concurrent-proof';
    $jwt = self::getContainer()->get(JWTTokenManagerInterface::class);
    $this->assertInstanceOf(JWTTokenManagerInterface::class, $jwt);
    $bearer = $jwt->create($this->user);
    $dispatcher = self::getContainer()->get('event_dispatcher');
    $this->assertInstanceOf(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class, $dispatcher);
    $listener = function (\Symfony\Component\HttpKernel\Event\ControllerEvent $event) use ($old_name, $new_name, $concurrent_proof): void {
      if ('/api/users/me' !== $event->getRequest()->getPathInfo()) {
        return;
      }
      $this->assertSame($old_name, $this->user->getUserIdentifier(), 'the request authenticated with the original identifier');
      // Simulate another committed rename after authentication, preserving the password and account ID.
      $this->entity_manager->createQuery('UPDATE '.User::class.' u SET u.username = :name, u.usernameCanonical = :canonical WHERE u.id = :id')
        ->setParameters(['name' => $new_name, 'canonical' => strtolower($new_name), 'id' => $this->user->getId()])->execute()
      ;
      $this->entity_manager->createQuery('DELETE FROM '.RefreshToken::class.' t WHERE t.username = :name')
        ->setParameter('name', $old_name)->execute()
      ;
      $proof = RefreshToken::createForUserWithTtl($concurrent_proof, $this->user, 3600);
      $proof->setUsername($new_name);
      $this->entity_manager->persist($proof);
      $this->entity_manager->flush();
      $event->getRequest()->attributes->set(\App\Security\Authentication\JwtRefresh\RefreshTokenService::REFRESHED_BEARER_COOKIE_ATTRIBUTE, 'stale-auto-refresh');
    };
    $dispatcher->addListener(\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER, $listener);
    $client = self::getContainer()->get('test.client');
    $this->assertInstanceOf(\Symfony\Bundle\FrameworkBundle\KernelBrowser::class, $client);
    $client->disableReboot();
    try {
      $client->request('PATCH', '/api/users/me', [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$bearer, 'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
      ], json_encode(['username' => $old_name, 'about' => 'Must not be saved'], JSON_THROW_ON_ERROR));
      $this->assertSame(401, $client->getResponse()->getStatusCode());
      $this->assertTrue($this->entity_manager->isOpen());
      $this->entity_manager->flush();
      $row = $this->entity_manager->createQuery('SELECT u.username, u.about FROM '.User::class.' u WHERE u.id = :id')
        ->setParameter('id', $this->user->getId())->getOneOrNullResult()
      ;
      $this->assertIsArray($row);
      $this->assertSame($new_name, $row['username']);
      $this->assertNull($row['about'], 'the stale profile callback must not run');
      $this->assertSame(0, $this->entity_manager->getRepository(RefreshToken::class)->count(['username' => $old_name]));
      $this->assertSame(1, $this->entity_manager->getRepository(RefreshToken::class)->count(['username' => $new_name]));
      $this->assertNotNull($this->entity_manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $concurrent_proof]));
      $this->assertCount(2, $client->getResponse()->headers->getCookies());
      foreach ($client->getResponse()->headers->getCookies() as $cookie) {
        $this->assertLessThan(time(), $cookie->getExpiresTime());
      }
    } finally {
      $dispatcher->removeListener(\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER, $listener);
    }
  }

  private function token(string $suffix): string
  {
    return $this->user->getUserIdentifier().'-'.$suffix;
  }

  private function countTokens(): int
  {
    return $this->entity_manager->getRepository(RefreshToken::class)->count(['username' => $this->user->getUserIdentifier()]);
  }
}
