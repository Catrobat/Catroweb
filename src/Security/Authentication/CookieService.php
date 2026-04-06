<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\RouterInterface;

readonly class CookieService
{
  public function __construct(
    #[Autowire('%env(JWT_TTL)%')]
    private int $jwtTokenLifetime,
    #[Autowire('%env(REFRESH_TOKEN_TTL)%')]
    private int $refreshTokenLifetime,
    private RouterInterface $router)
  {
  }

  /**
   * Create bearer token cookie.
   */
  public function createBearerTokenCookie(string $bearer_token): Cookie
  {
    return Cookie::create(
      'BEARER',
      $bearer_token,
      time() + $this->jwtTokenLifetime,
      $this->getCookiePath(),
      null,
      $this->isSecureCookie(),
      true,
      false,
      Cookie::SAMESITE_LAX
    );
  }

  /**
   * Create refresh token cookie.
   */
  public function createRefreshTokenCookie(string $refresh_token): Cookie
  {
    return Cookie::create(
      'REFRESH_TOKEN',
      $refresh_token,
      time() + $this->refreshTokenLifetime,
      $this->getCookiePath(),
      null,
      $this->isSecureCookie(),
      true,
      false,
      Cookie::SAMESITE_LAX
    );
  }

  public function createClearedCookie(string $cookie): Cookie
  {
    return Cookie::create(
      $cookie,
      '',
      time() - 3600,
      $this->getCookiePath(),
      null,
      $this->isSecureCookie(),
      true,
      false,
      $this->getSameSite($cookie)
    );
  }

  /**
   * @return array{0: Cookie, 1: Cookie}
   */
  public function createClearedAuthenticationCookies(): array
  {
    return [
      $this->createClearedCookie('BEARER'),
      $this->createClearedCookie('REFRESH_TOKEN'),
    ];
  }

  public function addClearedAuthenticationCookiesToHeader(array &$responseHeaders): void
  {
    $responseHeaders['Set-Cookie'] = $this->createClearedAuthenticationCookies();
  }

  public function clearCookie(string $cookie): void
  {
    if (isset($_COOKIE[$cookie])) {
      $same_site = $this->getSameSite($cookie);

      setcookie($cookie, '', ['expires' => time() - 3600, 'path' => $this->getCookiePath(), 'secure' => $this->isSecureCookie(), 'httponly' => true, 'samesite' => $same_site]);
      setcookie($cookie, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $this->isSecureCookie(), 'httponly' => true, 'samesite' => $same_site]);
      unset($_COOKIE[$cookie]);
    }
  }

  private function getCookiePath(): string
  {
    return $this->router->getContext()->getBaseUrl().'/';
  }

  private function isSecureCookie(): bool
  {
    return 'prod' === ($_ENV['APP_ENV'] ?? 'dev');
  }

  /**
   * @return 'lax'|'strict'
   */
  private function getSameSite(string $cookie): string
  {
    return Cookie::SAMESITE_LAX;
  }
}
