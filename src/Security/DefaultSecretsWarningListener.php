<?php

declare(strict_types=1);

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Detects when known development-only default secrets are used in non-dev environments
 * and logs critical warnings. These defaults are safe for local development but must
 * be overridden in production via .env.prod.local or real environment variables.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 512)]
class DefaultSecretsWarningListener
{
  /** @var array<string, string> Maps env var names to their known insecure default values */
  private const array DEFAULT_SECRETS = [
    'APP_SECRET' => '93055246cfa39f62f5be97928084989a',
    'JWT_PASSPHRASE' => 'catroweb',
  ];

  private bool $already_warned = false;

  public function __construct(
    #[Autowire('%kernel.environment%')]
    private readonly string $environment,
    #[Autowire('%env(APP_SECRET)%')]
    private readonly string $app_secret,
    #[Autowire('%env(JWT_PASSPHRASE)%')]
    private readonly string $jwt_passphrase,
    private readonly LoggerInterface $logger,
  ) {
  }

  public function __invoke(RequestEvent $event): void
  {
    if (!$event->isMainRequest()) {
      return;
    }

    if (\in_array($this->environment, ['dev', 'test'], true)) {
      return;
    }

    if ($this->already_warned) {
      return;
    }

    $this->already_warned = true;

    $insecure = $this->detectInsecureDefaults();
    if ([] === $insecure) {
      return;
    }

    $names = implode(', ', $insecure);
    $this->logger->critical(
      'SECURITY: Default development secrets detected in "{env}" environment: {names}. '
      .'Override these values in .env.prod.local or via real environment variables. '
      .'See docs/operations/Secret-Management.md for instructions.',
      [
        'env' => $this->environment,
        'names' => $names,
      ]
    );
  }

  /**
   * @return list<string> names of env vars still using insecure defaults
   */
  private function detectInsecureDefaults(): array
  {
    $insecure = [];

    if (hash_equals(self::DEFAULT_SECRETS['APP_SECRET'], $this->app_secret)) {
      $insecure[] = 'APP_SECRET';
    }

    if (hash_equals(self::DEFAULT_SECRETS['JWT_PASSPHRASE'], $this->jwt_passphrase)) {
      $insecure[] = 'JWT_PASSPHRASE';
    }

    return $insecure;
  }
}
