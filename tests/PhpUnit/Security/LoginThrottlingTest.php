<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\EventListener\LoginThrottlingListener;

/**
 * Password logins (web form and the app's API login) must be rate limited. The limits themselves are
 * config/packages/rate_limiter.php `login_local` / `login_global` (no_limit in the test env).
 *
 * @internal
 */
#[CoversNothing]
final class LoginThrottlingTest extends KernelTestCase
{
  /**
   * @return iterable<string, array{string}>
   */
  public static function provideLoginFirewalls(): iterable
  {
    yield 'app API login' => ['api_authentication_login'];
    yield 'web form login' => ['main'];
  }

  #[DataProvider('provideLoginFirewalls')]
  public function testThePasswordLoginIsThrottled(string $firewall): void
  {
    $dispatcher = self::getContainer()->get('security.event_dispatcher.'.$firewall);
    \assert($dispatcher instanceof EventDispatcherInterface);

    $listeners = array_map(
      static fn (mixed $listener): string => \is_array($listener) && \is_object($listener[0]) ? $listener[0]::class : get_debug_type($listener),
      $dispatcher->getListeners(CheckPassportEvent::class),
    );

    $this->assertContains(LoginThrottlingListener::class, $listeners);
  }
}
