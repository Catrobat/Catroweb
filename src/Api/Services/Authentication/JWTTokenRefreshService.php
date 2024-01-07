<?php

namespace App\Api\Services\Authentication;

use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTTokenRefreshService
{
  public function __construct(private readonly RefreshToken $refreshToken)
  {
  }

  public function refresh(Request $request): Response
  {
    if ($bearer = $request->cookies->get('REFRESH_TOKEN')) {
      $request->request->set('refresh_token', $bearer);
    }

    return $this->refreshToken->refresh($request);
  }
}
