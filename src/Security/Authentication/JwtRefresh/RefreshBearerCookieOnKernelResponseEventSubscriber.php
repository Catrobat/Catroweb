<?php

declare(strict_types=1);

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
    if ($this->refresh_token_service->isBearerCookieSet()) {
      return;
    }
    if (!$this->refresh_token_service->isRefreshTokenCookieSet($event)) {
      return;
    }
    // Bearer is missing, however we can try to create a new cookie one from the refresh token
    $this->refresh_token_service->refreshBearerCookie($event);
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::RESPONSE => ['onKernelResponse', 200]];
  }
}
