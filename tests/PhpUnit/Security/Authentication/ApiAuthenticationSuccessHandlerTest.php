<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\ApiAuthenticationSuccessHandler;
use App\Security\Authentication\AuthenticationModeResolver;
use App\Security\Authentication\MainFirewallSessionLogin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * @internal
 */
#[CoversClass(ApiAuthenticationSuccessHandler::class)]
final class ApiAuthenticationSuccessHandlerTest extends TestCase
{
  use AuthenticationTestFactory;

  #[Group('unit')]
  public function testDelegatesToInnerHandlerAndProcessesResponse(): void
  {
    $request = Request::create('/api/authentication', 'POST');
    $token = $this->createStub(TokenInterface::class);
    $response = new Response('{}');

    $inner_handler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
    $inner_handler
      ->expects($this->once())
      ->method('onAuthenticationSuccess')
      ->with($request, $token)
      ->willReturn($response)
    ;

    $handler = new ApiAuthenticationSuccessHandler(
      $inner_handler,
      new AuthenticationModeResolver(),
      $this->createMainFirewallSessionLogin(),
      $this->createResponseProcessor(),
    );

    $this->assertSame($response, $handler->onAuthenticationSuccess($request, $token));
  }

  #[Group('unit')]
  public function testCookieModeAlsoPersistsMainFirewallSession(): void
  {
    $request = Request::create('/api/authentication', 'POST');
    $request->headers->set(AuthenticationModeResolver::HEADER_NAME, AuthenticationModeResolver::COOKIE_MODE);
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);
    $session->start();
    $request->cookies->set($session->getName(), $session->getId());

    $token = new UsernamePasswordToken(
      new TestUser('cookie-user', ['ROLE_USER']),
      'api_authentication_login',
      ['ROLE_USER'],
    );
    $response = new Response('{"token":"jwt-token","refresh_token":"refresh-token"}', Response::HTTP_OK, ['Content-Type' => 'application/json']);

    $inner_handler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
    $inner_handler
      ->expects($this->once())
      ->method('onAuthenticationSuccess')
      ->with($request, $token)
      ->willReturn($response)
    ;

    $handler = new ApiAuthenticationSuccessHandler(
      $inner_handler,
      new AuthenticationModeResolver(),
      $this->createMainFirewallSessionLogin(),
      $this->createResponseProcessor(),
    );

    $processed_response = $handler->onAuthenticationSuccess($request, $token);

    $this->assertSame('{}', $processed_response->getContent());
    $stored_token = unserialize((string) $session->get('_security_main'), ['allowed_classes' => true]);
    self::assertInstanceOf(UsernamePasswordToken::class, $stored_token);
    self::assertSame('cookie-user', $stored_token->getUserIdentifier());
    self::assertSame('main', $stored_token->getFirewallName());
  }

  private function createMainFirewallSessionLogin(): MainFirewallSessionLogin
  {
    return new MainFirewallSessionLogin($this->createStub(SessionAuthenticationStrategyInterface::class));
  }
}
