<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\RouterInterface;

class CookieService
{
  private int $jwtTokenLifetime;
  private int $refreshTokenLifetime;
  private RouterInterface $router;

  public function __construct(int $jwtTokenLifetime, int $refreshTokenLifetime, RouterInterface $router)
  {
    $this->jwtTokenLifetime = $jwtTokenLifetime;
    $this->refreshTokenLifetime = $refreshTokenLifetime;
    $this->router = $router;
  }

  /**
   * Create bearer token cookie.
   */
  public function createBearerTokenCookie(string $bearer_token): Cookie
  {
    return new Cookie(
      'BEARER',
      $bearer_token,
      time() + $this->jwtTokenLifetime, // expiration
      $this->router->getContext()->getBaseUrl().'/', // path
      null, // domain, null means that Symfony will generate it on its own.
      'prod' === $_ENV['APP_ENV'], // secure (HTTPS only)
      false, // httpOnly
      false, // raw
      'lax' // same-site parameter, can be 'lax' or 'strict'.
    );
  }

  /**
   * Create refresh token cookie.
   */
  public function createRefreshTokenCookie(string $refresh_token): Cookie
  {
    return new Cookie(
      'REFRESH_TOKEN',
      $refresh_token,
      time() + $this->refreshTokenLifetime, // expiration
      $this->router->getContext()->getBaseUrl().'/', // path - optional /api/authentication
      null, // domain, null means that Symfony will generate it on its own.
      'prod' === $_ENV['APP_ENV'], // secure (HTTPS only)
      true, // httpOnly
      false, // raw
      'strict' // same-site parameter, can be 'lax' or 'strict'.
    );
  }
}
