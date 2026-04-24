<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, method: 'onKernelResponse')]
class SecurityHeadersSubscriber
{
  public function __construct(
    #[Autowire('%kernel.environment%')]
    private readonly string $kernelEnvironment,
  ) {
  }

  public function onKernelResponse(ResponseEvent $event): void
  {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();
    $headers = $response->headers;

    $headers->set('X-Content-Type-Options', 'nosniff');
    $headers->set('X-Frame-Options', 'SAMEORIGIN');
    $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

    $headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://*.bugsnag.com https://*.google-analytics.com https://*.googletagmanager.com https://appleid.apple.com; frame-ancestors 'self'");

    if ('prod' === $this->kernelEnvironment) {
      $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
  }
}
