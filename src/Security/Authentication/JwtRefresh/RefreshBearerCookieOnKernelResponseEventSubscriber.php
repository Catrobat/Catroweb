<?php

namespace App\Security\Authentication\JwtRefresh;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RefreshBearerCookieOnKernelResponseEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected RefreshTokenService $refresh_token_service)
  {
  }

  public function onKernelResponse(ResponseEvent $event): void
  {
    if (!$this->refresh_token_service->isBearerCookieSet() && $this->refresh_token_service->isRefreshTokenCookieSet($event)) {
      // Bearer is missing, however we can try to create a new cookie one from the refresh token
      $this->refresh_token_service->refreshBearerCookie($event);
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::RESPONSE => ['onKernelResponse', 200]];
  }
}
