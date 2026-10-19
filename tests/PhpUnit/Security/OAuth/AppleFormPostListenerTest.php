<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\OAuth;

use App\Security\OAuth\AppleFormPostListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(AppleFormPostListener::class)]
class AppleFormPostListenerTest extends TestCase
{
  public function testResubmitsCrossSitePostWithoutSessionCookie(): void
  {
    $event = $this->dispatch(Request::create('/login/check-apple', 'POST', [
      'code' => 'c0de',
      'state' => 'eyJjc3JmX3Rva2VuIjoiYWJjIn0%3D',
      'user' => '{"name":{"firstName":"<Ann>","lastName":"O\'Neil"}}',
    ]));

    $response = $event->getResponse();
    $this->assertNotNull($response);
    $html = (string) $response->getContent();
    $this->assertStringContainsString('<form method="post" action="/login/check-apple">', $html);
    $this->assertStringContainsString('name="code" value="c0de"', $html);
    $this->assertStringContainsString('name="state" value="eyJjc3JmX3Rva2VuIjoiYWJjIn0%3D"', $html);
    $this->assertStringContainsString('name="_resubmitted" value="1"', $html);
    $this->assertStringContainsString('&lt;Ann&gt;', $html);
    $this->assertStringNotContainsString('<Ann>', $html);
    $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
  }

  public function testLetsResubmittedPostThrough(): void
  {
    $event = $this->dispatch(Request::create('/login/check-apple', 'POST', ['code' => 'c0de', '_resubmitted' => '1']));

    $this->assertNull($event->getResponse());
  }

  public function testLetsPostWithSessionCookieThrough(): void
  {
    $request = Request::create('/login/check-apple', 'POST', ['code' => 'c0de']);
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);
    $request->cookies->set($session->getName(), 'abc');

    $this->assertNull($this->dispatch($request)->getResponse());
  }

  public function testIgnoresOtherPathsAndMethods(): void
  {
    $this->assertNull($this->dispatch(Request::create('/login/check-google', 'POST', ['code' => 'x']))->getResponse());
    $this->assertNull($this->dispatch(Request::create('/login/check-apple', 'GET', ['code' => 'x']))->getResponse());
  }

  private function dispatch(Request $request): RequestEvent
  {
    $event = new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);
    new AppleFormPostListener()($event);

    return $event;
  }
}
