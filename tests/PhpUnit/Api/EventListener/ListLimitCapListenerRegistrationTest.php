<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\EventListener;

use App\Api\EventListener\ListLimitCapListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[CoversClass(ListLimitCapListener::class)]
final class ListLimitCapListenerRegistrationTest extends KernelTestCase
{
  public function testTheCapRunsAfterRoutingAndBeforeTheController(): void
  {
    $dispatcher = self::getContainer()->get('event_dispatcher');
    \assert($dispatcher instanceof EventDispatcherInterface);

    // getListeners() returns the listeners in the order they run.
    $order = array_map(
      static fn (mixed $listener): string => \is_array($listener) && \is_object($listener[0]) ? $listener[0]::class : get_debug_type($listener),
      $dispatcher->getListeners(KernelEvents::REQUEST),
    );

    $cap = array_search(ListLimitCapListener::class, $order, true);
    $router = array_search(RouterListener::class, $order, true);
    $this->assertIsInt($cap, 'ListLimitCapListener is not registered for kernel.request');
    $this->assertIsInt($router);
    // After routing, so a percent-encoded path (/%61pi/...) is judged by the route it resolved to.
    $this->assertGreaterThan($router, $cap);
  }
}
