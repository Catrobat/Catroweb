<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\AuthenticationModeResolver;
use App\Security\Authentication\AuthenticationSuccessResponseProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(AuthenticationSuccessResponseProcessor::class)]
final class AuthenticationSuccessResponseProcessorTest extends TestCase
{
  use AuthenticationTestFactory;

  #[Group('unit')]
  public function testApiModeKeepsTokensInJsonBody(): void
  {
    $processor = $this->createResponseProcessor();
    $request = Request::create('/api/authentication', 'POST');
    $response = new Response('{"token":"jwt-token","refresh_token":"refresh-token"}', Response::HTTP_OK, ['Content-Type' => 'application/json']);

    $processed_response = $processor->process($request, $response);

    $this->assertSame('{"token":"jwt-token","refresh_token":"refresh-token"}', $processed_response->getContent());
    $this->assertCount(0, $processed_response->headers->getCookies());
  }

  #[Group('unit')]
  public function testCookieModeMovesTokensToCookies(): void
  {
    $processor = $this->createResponseProcessor();
    $request = Request::create('/api/authentication', 'POST');
    $request->headers->set(AuthenticationModeResolver::HEADER_NAME, AuthenticationModeResolver::COOKIE_MODE);
    $response = new Response('{"token":"jwt-token","refresh_token":"refresh-token"}', Response::HTTP_OK, ['Content-Type' => 'application/json']);

    $processed_response = $processor->process($request, $response);

    $this->assertSame('{}', $processed_response->getContent());
    $cookies = $processed_response->headers->getCookies();
    $this->assertCount(2, $cookies);
    $this->assertSame('BEARER', $cookies[0]->getName());
    $this->assertSame('REFRESH_TOKEN', $cookies[1]->getName());
  }
}
