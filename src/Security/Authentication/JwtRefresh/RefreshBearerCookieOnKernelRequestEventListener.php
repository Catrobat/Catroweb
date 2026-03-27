<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'refreshBearerCookie', priority: 200)]
class RefreshBearerCookieOnKernelRequestEventListener
{
  public function __construct(private readonly RefreshTokenService $refresh_token_service)
  {
  }

  public function refreshBearerCookie(RequestEvent $event): void
  {
    if (!$event->isMainRequest()) {
      return;
    }

    $this->refresh_token_service->refreshRequestAuthentication($event->getRequest());
  }
}
