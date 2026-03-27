<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, method: 'refreshBearerCookie', priority: 200)]
class RefreshBearerCookieOnKernelResponseEventListener
{
  public function __construct(protected RefreshTokenService $refresh_token_service)
  {
  }

  public function refreshBearerCookie(ResponseEvent $event): void
  {
    if (!$event->isMainRequest()) {
      return;
    }

    $this->refresh_token_service->syncAuthenticationCookies($event);
  }
}
