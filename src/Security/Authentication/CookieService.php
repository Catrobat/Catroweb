<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\RouterInterface;

readonly class CookieService
{
  public function __construct(private int $jwtTokenLifetime, private int $refreshTokenLifetime, private RouterInterface $router)
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
      // expiration
      $this->router->getContext()->getBaseUrl().'/',
      // path
      null,
      // domain, null means that Symfony will generate it on its own.
      'prod' === $_ENV['APP_ENV'],
      // secure (HTTPS only)
      false,
      // httpOnly
      false,
      // raw
      'lax'
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
      // expiration
      $this->router->getContext()->getBaseUrl().'/',
      // path - optional /api/authentication
      null,
      // domain, null means that Symfony will generate it on its own.
      'prod' === $_ENV['APP_ENV'],
      // secure (HTTPS only)
      true,
      // httpOnly
      false,
      // raw
      'strict'
    );
  }

  public function clearCookie(string $cookie): void
  {
    if (isset($_COOKIE[$cookie])) {
      setcookie($cookie, '', ['expires' => time() - 3600, 'path' => $this->router->getContext()->getBaseUrl().'/']);
      setcookie($cookie, '', ['expires' => time() - 3600, 'path' => '/']);
      unset($_COOKIE[$cookie]);
    }
  }
}
