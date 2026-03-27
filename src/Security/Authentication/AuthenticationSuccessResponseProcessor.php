<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class AuthenticationSuccessResponseProcessor
{
  public function __construct(
    private AuthenticationModeResolver $mode_resolver,
    private CookieService $cookie_service,
  ) {
  }

  public function process(Request $request, Response $response): Response
  {
    if (!$this->mode_resolver->isCookieMode($request)) {
      return $response;
    }

    $response_data = json_decode((string) $response->getContent(), true);
    if (!\is_array($response_data)) {
      return $response;
    }

    $bearer_token = $response_data['token'] ?? null;
    if (\is_string($bearer_token) && '' !== $bearer_token) {
      $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($bearer_token));
      unset($response_data['token']);
    }

    $refresh_token = $response_data['refresh_token'] ?? null;
    if (\is_string($refresh_token) && '' !== $refresh_token) {
      $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refresh_token));
      unset($response_data['refresh_token']);
    }

    $response->setContent([] === $response_data ? '{}' : json_encode($response_data, \JSON_THROW_ON_ERROR));

    return $response;
  }
}
