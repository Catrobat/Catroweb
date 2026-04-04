<?php

declare(strict_types=1);

namespace App\Admin\System\Maintenance;

use App\DB\Entity\Project\Program;
use App\System\Mail\EmailBudgetManager;
use Doctrine\ORM\EntityManagerInterface;

class SystemHealthService
{
  public const float STORAGE_EMERGENCY_PERCENT = 95.0;
  public const float STORAGE_CRITICAL_PERCENT = 90.0;
  public const float STORAGE_WARNING_PERCENT = 80.0;
  public const int STORAGE_EMERGENCY_FREE_BYTES = 1_073_741_824;
  public const int STORAGE_CRITICAL_FREE_BYTES = 5_368_709_120;
  public const int STORAGE_WARNING_FREE_BYTES = 10_737_418_240;

  private const int EMAIL_LOW_THRESHOLD = 30;
  private const int EMAIL_MODERATE_THRESHOLD = 100;

  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly EmailBudgetManager $emailBudgetManager,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function getEmailBudget(): array
  {
    $remaining = $this->emailBudgetManager->getRemainingBudget();
    $totalRemaining = $remaining['total'];
    $totalSent = EmailBudgetManager::DAILY_LIMIT - $totalRemaining;

    $breakdown = [];
    foreach (EmailBudgetManager::TYPE_RESERVES as $type => $reserve) {
      $typeRemaining = $remaining[$type];
      $breakdown[$type] = [
        'sent' => $reserve - $typeRemaining,
        'reserve' => $reserve,
        'remaining' => $typeRemaining,
      ];
    }

    return [
      'daily_limit' => EmailBudgetManager::DAILY_LIMIT,
      'sent_today' => $totalSent,
      'remaining' => $totalRemaining,
      'breakdown' => $breakdown,
    ];
  }

  /**
   * @return array<string, int>
   */
  public function getProjectCounts(): array
  {
    $total = (int) $this->entityManager->createQueryBuilder()
      ->select('COUNT(p.id)')
      ->from(Program::class, 'p')
      ->getQuery()
      ->getSingleScalarResult()
    ;

    $visible = (int) $this->entityManager->createQueryBuilder()
      ->select('COUNT(p.id)')
      ->from(Program::class, 'p')
      ->where('p.visible = true')
      ->andWhere('p.auto_hidden = false')
      ->andWhere('p.private = false')
      ->getQuery()
      ->getSingleScalarResult()
    ;

    $private = (int) $this->entityManager->createQueryBuilder()
      ->select('COUNT(p.id)')
      ->from(Program::class, 'p')
      ->where('p.private = true')
      ->getQuery()
      ->getSingleScalarResult()
    ;

    $hidden = (int) $this->entityManager->createQueryBuilder()
      ->select('COUNT(p.id)')
      ->from(Program::class, 'p')
      ->where('p.visible = false OR p.auto_hidden = true')
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return [
      'total' => $total,
      'visible' => $visible,
      'private' => $private,
      'hidden' => $hidden,
    ];
  }

  public function getStoragePressureLevel(float $percentage, int $freeSpace): string
  {
    return match (true) {
      $percentage >= self::STORAGE_EMERGENCY_PERCENT || $freeSpace < self::STORAGE_EMERGENCY_FREE_BYTES => 'emergency',
      $percentage >= self::STORAGE_CRITICAL_PERCENT || $freeSpace < self::STORAGE_CRITICAL_FREE_BYTES => 'critical',
      $percentage >= self::STORAGE_WARNING_PERCENT || $freeSpace < self::STORAGE_WARNING_FREE_BYTES => 'warning',
      default => 'normal',
    };
  }

  public function getEmailBudgetLevel(): string
  {
    $remaining = $this->emailBudgetManager->getRemainingBudget();
    $totalRemaining = $remaining['total'];

    return match (true) {
      $totalRemaining < self::EMAIL_LOW_THRESHOLD => 'low',
      $totalRemaining < self::EMAIL_MODERATE_THRESHOLD => 'moderate',
      default => 'ok',
    };
  }
}
