<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\Security\DefaultSecretsWarningListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(DefaultSecretsWarningListener::class)]
class DefaultSecretsWarningListenerTest extends TestCase
{
  private const string DEFAULT_APP_SECRET = '93055246cfa39f62f5be97928084989a';
  private const string DEFAULT_JWT_PASSPHRASE = 'catroweb';
  private const string SAFE_SECRET = 'a-real-production-secret-value-here';

  public function testLogsWarningInProdWithDefaultSecrets(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('critical')
      ->with(
        $this->stringContains('Default development secrets detected'),
        $this->callback(static fn (array $ctx): bool => 'prod' === $ctx['env']
          && str_contains($ctx['names'], 'APP_SECRET')
          && str_contains($ctx['names'], 'JWT_PASSPHRASE'))
      )
    ;

    $listener = new DefaultSecretsWarningListener(
      'prod',
      self::DEFAULT_APP_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $listener($this->createRequestEvent());
  }

  public function testSkipsWarningInDevEnvironment(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('critical');

    $listener = new DefaultSecretsWarningListener(
      'dev',
      self::DEFAULT_APP_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $listener($this->createRequestEvent());
  }

  public function testSkipsWarningInTestEnvironment(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('critical');

    $listener = new DefaultSecretsWarningListener(
      'test',
      self::DEFAULT_APP_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $listener($this->createRequestEvent());
  }

  public function testNoWarningWhenSecretsAreOverridden(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('critical');

    $listener = new DefaultSecretsWarningListener(
      'prod',
      self::SAFE_SECRET,
      self::SAFE_SECRET,
      $logger,
    );

    $listener($this->createRequestEvent());
  }

  public function testWarnsOnlyForSecretsThatMatchDefaults(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('critical')
      ->with(
        $this->anything(),
        $this->callback(static fn (array $ctx): bool => 'JWT_PASSPHRASE' === $ctx['names'])
      )
    ;

    $listener = new DefaultSecretsWarningListener(
      'prod',
      self::SAFE_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $listener($this->createRequestEvent());
  }

  public function testWarnsOnlyOncePerProcessLifetime(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('critical');

    $listener = new DefaultSecretsWarningListener(
      'prod',
      self::DEFAULT_APP_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $listener($this->createRequestEvent());
    $listener($this->createRequestEvent());
    $listener($this->createRequestEvent());
  }

  public function testSkipsSubRequests(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('critical');

    $listener = new DefaultSecretsWarningListener(
      'prod',
      self::DEFAULT_APP_SECRET,
      self::DEFAULT_JWT_PASSPHRASE,
      $logger,
    );

    $kernel = $this->createStub(HttpKernelInterface::class);
    $event = new RequestEvent($kernel, new Request(), HttpKernelInterface::SUB_REQUEST);

    $listener($event);
  }

  private function createRequestEvent(): RequestEvent
  {
    $kernel = $this->createStub(HttpKernelInterface::class);

    return new RequestEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST);
  }
}
