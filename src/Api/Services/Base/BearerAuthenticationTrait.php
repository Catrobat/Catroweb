<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use App\Api\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait BearerAuthenticationTrait.
 */
trait BearerAuthenticationTrait
{
  private string $token;

  /**
   * @throws \Exception
   */
  public function setBearerAuth(?string $value): void
  {
    if (null !== $value && '' !== trim($value)) {
      $this->token = $this->extractAuthenticationToken($value);

      return;
    }

    $cookie_token = $this->getBearerCookieToken();
    if ('' === $cookie_token) {
      throw new ApiException('The route must be registered under the jwt_token_authenticator! (security.yaml)', Response::HTTP_UNAUTHORIZED);
    }

    $this->token = $cookie_token;
  }

  public function getAuthenticationToken(): string
  {
    return $this->token;
  }

  /**
   * @throws \Exception
   */
  private function extractAuthenticationToken(string $value): string
  {
    $split = preg_split('#\s+#', trim($value), 2);
    if (!\is_array($split)) {
      throw new ApiException('The route must be registered under the jwt_token_authenticator! (security.yaml)', Response::HTTP_UNAUTHORIZED);
    }

    $token = $split[1] ?? '';
    if ('' === $token) {
      throw new ApiException('The route must be registered under the jwt_token_authenticator! (security.yaml)', Response::HTTP_UNAUTHORIZED);
    }

    return $token;
  }

  protected function getBearerCookieToken(): string
  {
    return strval($this->getCurrentRequest()?->cookies->get('BEARER'));
  }

  protected function getCurrentRequest(): ?Request
  {
    return null;
  }
}
