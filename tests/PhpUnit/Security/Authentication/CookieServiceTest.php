<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\CookieService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CookieService::class)]
final class CookieServiceTest extends TestCase
{
  use AuthenticationTestFactory;

  #[Group('unit')]
  public function testBearerCookieIsHttpOnly(): void
  {
    $cookie = $this->createCookieService()->createBearerTokenCookie('jwt-token');

    $this->assertSame('BEARER', $cookie->getName());
    $this->assertTrue($cookie->isHttpOnly());
    $this->assertSame('/base/', $cookie->getPath());
  }

  #[Group('unit')]
  public function testRefreshCookieIsHttpOnly(): void
  {
    $cookie = $this->createCookieService()->createRefreshTokenCookie('refresh-token');

    $this->assertSame('REFRESH_TOKEN', $cookie->getName());
    $this->assertTrue($cookie->isHttpOnly());
    $this->assertSame('/base/', $cookie->getPath());
    $this->assertSame('lax', $cookie->getSameSite());
  }

  #[Group('unit')]
  public function testClearedRefreshCookieKeepsStrictSameSite(): void
  {
    $cookie = $this->createCookieService()->createClearedCookie('REFRESH_TOKEN');

    $this->assertSame('REFRESH_TOKEN', $cookie->getName());
    $this->assertTrue($cookie->isHttpOnly());
    $this->assertSame('/base/', $cookie->getPath());
    $this->assertSame('lax', $cookie->getSameSite());
  }
}
