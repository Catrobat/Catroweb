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
    #[Autowire('%env(bool:CAPTCHA_ENABLED)%')]
    private readonly bool $captchaEnabled = false,
    #[Autowire('%env(CAPTCHA_PUBLIC_URL)%')]
    private readonly string $captchaPublicUrl = '',
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

    $headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy());

    if ('prod' === $this->kernelEnvironment) {
      $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }
  }

  private function buildContentSecurityPolicy(): string
  {
    $connect_src = [
      "'self'",
      'https://*.bugsnag.com',
      'https://*.google-analytics.com',
      'https://*.googletagmanager.com',
      'https://appleid.apple.com',
    ];
    $worker_src = ["'self'"];

    // The Cap captcha (<cap-widget>) talks to its self-hosted backend, fetches
    // its proof-of-work WASM solver from the jsDelivr CDN, and runs the solver
    // in a Web Worker spawned from a blob: URL. Whitelist exactly those when
    // the captcha is enabled; the backend host is derived from the configured
    // public URL so the CSP can never drift from the widget's endpoint.
    if ($this->captchaEnabled) {
      $captcha_origin = $this->originFromUrl($this->captchaPublicUrl);
      if (null !== $captcha_origin) {
        $connect_src[] = $captcha_origin;
      }
      $connect_src[] = 'https://cdn.jsdelivr.net';
      $worker_src[] = 'blob:';
    }

    return implode('; ', [
      "default-src 'self'",
      "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com",
      "style-src 'self' 'unsafe-inline'",
      'img-src '."'self'".' data: https:',
      'font-src '."'self'".' data:',
      'connect-src '.implode(' ', $connect_src),
      'worker-src '.implode(' ', $worker_src),
      "frame-ancestors 'self'",
    ]);
  }

  /**
   * Reduces a URL to its CSP source origin (scheme://host[:port]), or null when
   * the URL is empty or unparseable.
   */
  private function originFromUrl(string $url): ?string
  {
    $parts = parse_url($url);
    if (false === $parts || empty($parts['scheme']) || empty($parts['host'])) {
      return null;
    }

    $origin = $parts['scheme'].'://'.$parts['host'];
    if (isset($parts['port'])) {
      $origin .= ':'.$parts['port'];
    }

    return $origin;
  }
}
