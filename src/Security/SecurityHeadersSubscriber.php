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
    // No self-embedding iframes exist in templates; DENY is safe and aligns with frame-ancestors policy.
    $headers->set('X-Frame-Options', 'DENY');
    $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $headers->set(
      'Permissions-Policy',
      'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=(), interest-cohort=()'
    );

    // Isolate browsing context group from cross-origin popups (mitigates Spectre-class attacks, tab-napping).
    $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
    // same-site (not same-origin) so resources can be loaded by sibling Catrobat subdomains.
    $headers->set('Cross-Origin-Resource-Policy', 'same-site');

    $headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://*.bugsnag.com https://*.google-analytics.com https://*.googletagmanager.com https://appleid.apple.com; frame-ancestors 'self'");

    if ('prod' === $this->kernelEnvironment) {
      $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }
  }
}
