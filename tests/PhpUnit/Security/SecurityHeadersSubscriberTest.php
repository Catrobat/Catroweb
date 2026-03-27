<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\Security\SecurityHeadersSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(SecurityHeadersSubscriber::class)]
class SecurityHeadersSubscriberTest extends TestCase
{
  public function testAddsSecurityHeadersOnMainRequest(): void
  {
    $subscriber = new SecurityHeadersSubscriber('dev');
    $event = $this->createResponseEvent(HttpKernelInterface::MAIN_REQUEST);

    $subscriber->onKernelResponse($event);

    $response = $event->getResponse();
    $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    $this->assertSame('camera=(), microphone=(), geolocation=()', $response->headers->get('Permissions-Policy'));
  }

  public function testSkipsSubRequests(): void
  {
    $subscriber = new SecurityHeadersSubscriber('prod');
    $event = $this->createResponseEvent(HttpKernelInterface::SUB_REQUEST);

    $subscriber->onKernelResponse($event);

    $response = $event->getResponse();
    $this->assertNull($response->headers->get('X-Content-Type-Options'));
  }

  public function testAddsHstsInProdOnly(): void
  {
    $devSubscriber = new SecurityHeadersSubscriber('dev');
    $devEvent = $this->createResponseEvent(HttpKernelInterface::MAIN_REQUEST);
    $devSubscriber->onKernelResponse($devEvent);
    $this->assertNull($devEvent->getResponse()->headers->get('Strict-Transport-Security'));

    $prodSubscriber = new SecurityHeadersSubscriber('prod');
    $prodEvent = $this->createResponseEvent(HttpKernelInterface::MAIN_REQUEST);
    $prodSubscriber->onKernelResponse($prodEvent);
    $this->assertSame(
      'max-age=31536000; includeSubDomains',
      $prodEvent->getResponse()->headers->get('Strict-Transport-Security')
    );
  }

  public function testNoHstsInTestEnv(): void
  {
    $subscriber = new SecurityHeadersSubscriber('test');
    $event = $this->createResponseEvent(HttpKernelInterface::MAIN_REQUEST);

    $subscriber->onKernelResponse($event);

    $this->assertNull($event->getResponse()->headers->get('Strict-Transport-Security'));
  }

  private function createResponseEvent(int $requestType): ResponseEvent
  {
    $kernel = $this->createStub(HttpKernelInterface::class);
    $request = new Request();
    $response = new Response();

    return new ResponseEvent($kernel, $request, $requestType, $response);
  }
}
