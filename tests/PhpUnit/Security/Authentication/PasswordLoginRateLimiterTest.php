<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\PasswordLoginRateLimiter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter;

/**
 * @internal
 */
#[CoversClass(PasswordLoginRateLimiter::class)]
final class PasswordLoginRateLimiterTest extends TestCase
{
  public function testFailedPasswordLoginsAreLimited(): void
  {
    $limiter = $this->limiter();
    $login = $this->request('/api/authentication');

    $limiter->consume($login);
    $limiter->consume($login);

    $this->assertFalse($limiter->consume($login)->isAccepted());
    $this->assertSame(0, $limiter->peek($login)->getRemainingTokens());
  }

  public function testTokenRefreshesNeitherCountNorAreBlocked(): void
  {
    $limiter = $this->limiter();
    $refresh = $this->request('/api/authentication/refresh');

    for ($i = 0; $i < 5; ++$i) {
      $this->assertTrue($limiter->consume($refresh)->isAccepted());
    }

    $this->assertTrue($limiter->consume($this->request('/api/authentication'))->isAccepted());
  }

  private function limiter(): PasswordLoginRateLimiter
  {
    $factory = static fn (string $id, int $limit): RateLimiterFactory => new RateLimiterFactory(
      ['id' => $id, 'policy' => 'sliding_window', 'limit' => $limit, 'interval' => '1 minute'],
      new InMemoryStorage(),
    );

    return new PasswordLoginRateLimiter(new DefaultLoginRateLimiter($factory('global', 10), $factory('local', 2), 'secret'));
  }

  private function request(string $path): Request
  {
    $request = Request::create($path, 'POST');
    $request->attributes->set('_security.last_username', 'kid');

    return $request;
  }
}
