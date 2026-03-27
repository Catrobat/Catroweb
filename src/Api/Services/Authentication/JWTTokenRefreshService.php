<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Exceptions\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Placeholder controller for the /api/authentication/refresh route.
 *
 * The actual refresh token logic is handled by the refresh_jwt authenticator
 * in the security firewall (gesdinet/jwt-refresh-token-bundle 2.x).
 * This controller is only reached if the authenticator does not intercept the request.
 */
readonly class JWTTokenRefreshService
{
  public function refresh(): Response
  {
    return ApiErrorResponse::create(
      Response::HTTP_UNAUTHORIZED,
      'unauthorized',
      'Refresh token not provided or invalid.'
    );
  }
}
