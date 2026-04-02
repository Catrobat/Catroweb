<?php

declare(strict_types=1);

namespace App\System\Mail;

use App\DB\Entity\System\EmailDailyBudget;
use App\DB\EntityRepository\System\EmailDailyBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EmailBudgetManager
{
  public const int DAILY_LIMIT = 300;

  public const array TYPE_RESERVES = [
    'verification' => 150,
    'reset' => 30,
    'consent' => 30,
    'admin' => 50,
    'management' => 40,
  ];

  public function __construct(
    protected EmailDailyBudgetRepository $repository,
    protected EntityManagerInterface $entityManager,
    protected LoggerInterface $logger,
  ) {
  }

  public function canSend(string $type): bool
  {
    $this->validateType($type);

    $budget = $this->repository->findOrCreateToday();

    if ($budget->getTotalSent() >= self::DAILY_LIMIT) {
      return false;
    }

    $typeReserve = self::TYPE_RESERVES[$type];
    $typeSent = $budget->getSentByType($type);

    if ($typeSent >= $typeReserve) {
      $sharedPool = self::DAILY_LIMIT - $this->getTotalReserved();
      $totalUsedFromShared = $this->getSharedPoolUsage($budget);

      return $totalUsedFromShared < $sharedPool;
    }

    return true;
  }

  public function recordSend(string $type): void
  {
    $this->validateType($type);

    $budget = $this->repository->findOrCreateToday();
    $budget->incrementType($type);
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

    $remaining['total'] = max(0, self::DAILY_LIMIT - $budget->getTotalSent());

    return $remaining;
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
