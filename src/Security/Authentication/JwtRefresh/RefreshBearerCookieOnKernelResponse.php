<?php

namespace App\Security\Authentication\JwtRefresh;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RefreshBearerCookieOnKernelResponse
{
  public function __construct(protected RefreshTokenService $refresh_token_service)
  {
  }

  public function onKernelResponse(ResponseEvent $event): void
  {
    if (!$this->refresh_token_service->isBearerCookieSet($event) && $this->refresh_token_service->isRefreshTokenCookieSet($event)) {
      // Bearer is missing, however we can try to create a new cookie one from the refresh token
      $this->refresh_token_service->refreshBearerCookie($event);
    }
  }
}
