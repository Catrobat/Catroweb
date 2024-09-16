<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class JWTTokenRefreshService
{
  public function __construct(
    #[Autowire(service: 'gesdinet.jwtrefreshtoken')]
    private RefreshToken $refreshToken,
  ) {
  }

  public function refresh(Request $request): Response
  {
    if ($bearer = $request->cookies->get('REFRESH_TOKEN')) {
      $request->request->set('refresh_token', $bearer);
    }

    return $this->refreshToken->refresh($request);
  }
}
