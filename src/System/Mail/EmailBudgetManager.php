<?php

declare(strict_types=1);

namespace App\System\Mail;

use App\DB\Entity\System\EmailDailyBudget;
use App\DB\EntityRepository\System\EmailDailyBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EmailBudgetManager
{
  /**
   * Mail transport slots; config/packages/mailer.php gives each one a DSN env var ("main" uses MAILER_DSN,
   * the others MAILER_DSN_<NAME>). MAILER_PROVIDERS picks which slots are used, in which order, and how many
   * mails a day each may send. See docs/operations/Email-Providers.md.
   */
  public const array PROVIDERS = ['main', 'mailjet', 'mailtrap', 'resend', 'mailgun', 'smtp2go', 'ses'];

  public const array TYPE_RESERVES = [
    'verification' => 150,
    'reset' => 30,
    'consent' => 30,
    'admin' => 50,
    'management' => 40,
  ];

  /** @var array<string, int> daily limit per provider, in the order they are tried */
  private readonly array $providerLimits;

  public function __construct(
    protected EmailDailyBudgetRepository $repository,
    protected EntityManagerInterface $entityManager,
    protected LoggerInterface $logger,
    #[Autowire('%env(MAILER_PROVIDERS)%')]
    string $providers = 'main:300',
  ) {
    $this->providerLimits = $this->parseProviders($providers);
  }

  public function getDailyLimit(): int
  {
    return array_sum($this->providerLimits);
  }

  /**
   * Providers that have not used up today's limit, in the order they should be tried.
   *
   * @return list<string>
   */
  public function getProvidersWithRoom(): array
  {
    $budget = $this->repository->findOrCreateToday();

    return array_keys(array_filter(
      $this->providerLimits,
      static fn (int $limit, string $provider): bool => $budget->getSentByProvider($provider) < $limit,
      ARRAY_FILTER_USE_BOTH,
    ));
  }

  public function canSend(string $type): bool
  {
    $this->validateType($type);

    $budget = $this->repository->findOrCreateToday();

    if ($budget->getTotalSent() >= $this->getDailyLimit()) {
      return false;
    }

    $typeReserve = self::TYPE_RESERVES[$type];
    $typeSent = $budget->getSentByType($type);

    if ($typeSent >= $typeReserve) {
      $sharedPool = $this->getDailyLimit() - $this->getTotalReserved();
      $totalUsedFromShared = $this->getSharedPoolUsage($budget);

      return $totalUsedFromShared < $sharedPool;
    }

    return true;
  }

  public function recordSend(string $type, string $provider = 'main'): void
  {
    $this->validateType($type);

    $budget = $this->repository->findOrCreateToday();
    $budget->incrementType($type);
    $budget->incrementProvider($provider);
    $this->entityManager->flush();
  }

  /**
   * @return array<string, int>
   */
  public function getRemainingBudget(): array
  {
    $budget = $this->repository->findOrCreateToday();
    $remaining = [];

    foreach (self::TYPE_RESERVES as $type => $reserve) {
      $sent = $budget->getSentByType($type);
      $remaining[$type] = max(0, $reserve - $sent);
    }

    $remaining['total'] = max(0, $this->getDailyLimit() - $budget->getTotalSent());

    return $remaining;
  }

  /**
   * @return array<string, int>
   */
  private function parseProviders(string $providers): array
  {
    $limits = [];
    foreach (array_filter(array_map(trim(...), explode(',', $providers))) as $entry) {
      [$name, $limit] = array_pad(explode(':', $entry, 2), 2, '');
      if (!in_array($name, self::PROVIDERS, true) || !ctype_digit($limit)) {
        $this->logger->error(sprintf('MAILER_PROVIDERS: ignoring "%s"; expected <provider>:<daily limit> with provider one of %s', $entry, implode(', ', self::PROVIDERS)));
        continue;
      }

      $limits[$name] = (int) $limit;
    }

    return $limits;
  }

  private function validateType(string $type): void
  {
    if (!isset(self::TYPE_RESERVES[$type])) {
      throw new \InvalidArgumentException(sprintf('Unknown email type: %s. Valid types: %s', $type, implode(', ', array_keys(self::TYPE_RESERVES))));
    }
  }

  private function getTotalReserved(): int
  {
    return array_sum(self::TYPE_RESERVES);
  }

  private function getSharedPoolUsage(EmailDailyBudget $budget): int
  {
    $overflow = 0;
    foreach (self::TYPE_RESERVES as $type => $reserve) {
      $sent = $budget->getSentByType($type);
      if ($sent > $reserve) {
        $overflow += $sent - $reserve;
      }
    }

    return $overflow;
  }
}
