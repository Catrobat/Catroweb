<?php

namespace App\Catrobat\Services;

use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Symfony\Component\HttpFoundation\Request;

class JWTTokenRefreshService
{
  private RefreshToken $refreshToken;

  public function __construct(RefreshToken $refreshToken)
  {
    $this->refreshToken = $refreshToken;
  }

  /**
   * Refresh token.
   *
   * @return mixed
   */
  public function refresh(Request $request)
  {
    if ($bearer = $request->cookies->get('REFRESH_TOKEN')) {
      $request->request->set('refresh_token', $bearer);
    }

    return $this->refreshToken->refresh($request);
  }
}
