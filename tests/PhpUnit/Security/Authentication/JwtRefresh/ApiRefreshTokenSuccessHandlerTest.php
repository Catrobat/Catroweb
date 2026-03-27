<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication\JwtRefresh;

use App\Security\Authentication\JwtRefresh\ApiRefreshTokenSuccessHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Tests\PhpUnit\Security\Authentication\AuthenticationTestFactory;

/**
 * @internal
 */
#[CoversClass(ApiRefreshTokenSuccessHandler::class)]
final class ApiRefreshTokenSuccessHandlerTest extends TestCase
{
  use AuthenticationTestFactory;

  #[Group('unit')]
  public function testDelegatesToInnerHandlerAndProcessesResponse(): void
  {
    $request = Request::create('/api/authentication/refresh', 'POST');
    $token = $this->createStub(TokenInterface::class);
    $response = new Response('{}');

    $inner_handler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
    $inner_handler
      ->expects($this->once())
      ->method('onAuthenticationSuccess')
      ->with($request, $token)
      ->willReturn($response)
    ;

    $handler = new ApiRefreshTokenSuccessHandler($inner_handler, $this->createResponseProcessor());

    $this->assertSame($response, $handler->onAuthenticationSuccess($request, $token));
  }
}
