<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;

final class AuthenticationModeResolver
{
  public const string HEADER_NAME = 'X-Auth-Mode';
  public const string API_MODE = 'api';
  public const string COOKIE_MODE = 'cookie';

  /**
   * @return self::API_MODE|self::COOKIE_MODE
   */
  public function resolve(?Request $request): string
  {
    $mode = strtolower(trim((string) $request?->headers->get(self::HEADER_NAME, self::API_MODE)));

    return self::COOKIE_MODE === $mode ? self::COOKIE_MODE : self::API_MODE;
  }

  public function isCookieMode(?Request $request): bool
  {
    return self::COOKIE_MODE === $this->resolve($request);
  }
}
