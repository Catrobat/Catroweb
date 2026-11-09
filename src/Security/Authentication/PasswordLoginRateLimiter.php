<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\RateLimiter\PeekableRequestRateLimiterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * login_throttling runs for every authenticator on its firewall, so on api_authentication_login it would also
 * count token refreshes (/api/authentication/refresh). Only the password login itself is limited here.
 */
final readonly class PasswordLoginRateLimiter implements PeekableRequestRateLimiterInterface
{
  public const string PASSWORD_LOGIN_PATH = '/api/authentication';

  public function __construct(private PeekableRequestRateLimiterInterface $password_logins)
  {
  }

  #[\Override]
  public function consume(Request $request): RateLimit
  {
    return $this->isPasswordLogin($request) ? $this->password_logins->consume($request) : self::unlimited();
  }

  #[\Override]
  public function peek(Request $request): RateLimit
  {
    return $this->isPasswordLogin($request) ? $this->password_logins->peek($request) : self::unlimited();
  }

  #[\Override]
  public function reset(Request $request): void
  {
    if ($this->isPasswordLogin($request)) {
      $this->password_logins->reset($request);
    }
  }

  private function isPasswordLogin(Request $request): bool
  {
    return self::PASSWORD_LOGIN_PATH === rtrim($request->getPathInfo(), '/');
  }

  private static function unlimited(): RateLimit
  {
    return new RateLimit(PHP_INT_MAX, new \DateTimeImmutable(), true, PHP_INT_MAX);
  }
}
