<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\AuthenticationModeResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(AuthenticationModeResolver::class)]
final class AuthenticationModeResolverTest extends TestCase
{
  #[Group('unit')]
  public function testMissingHeaderDefaultsToApiMode(): void
  {
    $resolver = new AuthenticationModeResolver();

    $this->assertSame(AuthenticationModeResolver::API_MODE, $resolver->resolve(Request::create('/api/authentication', 'POST')));
  }

  #[Group('unit')]
  public function testCookieHeaderEnablesCookieMode(): void
  {
    $resolver = new AuthenticationModeResolver();
    $request = Request::create('/api/authentication', 'POST');
    $request->headers->set(AuthenticationModeResolver::HEADER_NAME, 'cookie');

    $this->assertTrue($resolver->isCookieMode($request));
  }
}
