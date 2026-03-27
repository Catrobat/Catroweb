<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\MainFirewallSessionLogin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * @internal
 */
#[CoversClass(MainFirewallSessionLogin::class)]
final class MainFirewallSessionLoginTest extends TestCase
{
  #[Group('unit')]
  public function testStoresMainFirewallTokenInSessionAndMigratesExistingSession(): void
  {
    $request = Request::create('/api/authentication', 'POST');
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);
    $session->start();
    $request->cookies->set($session->getName(), $session->getId());

    $strategy = $this->createMock(SessionAuthenticationStrategyInterface::class);
    $strategy
      ->expects($this->once())
      ->method('onAuthentication')
      ->with(
        $request,
        $this->callback(static function (TokenInterface $token): bool {
          return $token instanceof UsernamePasswordToken
            && 'main' === $token->getFirewallName()
            && 'admin' === $token->getUserIdentifier();
        })
      )
    ;

    $service = new MainFirewallSessionLogin($strategy);
    $service->login($request, new UsernamePasswordToken(new TestUser('admin', ['ROLE_ADMIN']), 'api_authentication_login', ['ROLE_ADMIN']));

    $stored_token = unserialize((string) $session->get('_security_main'), ['allowed_classes' => true]);
    self::assertInstanceOf(UsernamePasswordToken::class, $stored_token);
    self::assertSame('admin', $stored_token->getUserIdentifier());
    self::assertSame('main', $stored_token->getFirewallName());
  }

  #[Group('unit')]
  public function testStartsSessionWithoutMigrationWhenNoPreviousSessionExists(): void
  {
    $request = Request::create('/api/authentication', 'POST');
    $request->setSession(new Session(new MockArraySessionStorage()));

    $strategy = $this->createMock(SessionAuthenticationStrategyInterface::class);
    $strategy->expects($this->never())->method('onAuthentication');

    $service = new MainFirewallSessionLogin($strategy);
    $service->login($request, new UsernamePasswordToken(new TestUser('user', ['ROLE_USER']), 'api_authentication_login', ['ROLE_USER']));

    $stored_token = unserialize((string) $request->getSession()->get('_security_main'), ['allowed_classes' => true]);
    self::assertInstanceOf(UsernamePasswordToken::class, $stored_token);
    self::assertSame('user', $stored_token->getUserIdentifier());
  }
}
