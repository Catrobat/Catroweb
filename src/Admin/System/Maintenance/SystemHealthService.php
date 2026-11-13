<?php

declare(strict_types=1);

namespace App\Admin\System\Maintenance;

use App\DB\Entity\Project\Project;
use App\System\Mail\EmailBudgetManager;
use Doctrine\ORM\EntityManagerInterface;

class SystemHealthService
{
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
    $totalSent = $this->emailBudgetManager->getDailyLimit() - $totalRemaining;

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
      'daily_limit' => $this->emailBudgetManager->getDailyLimit(),
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
    $conn = $this->entityManager->getConnection();
    $table = $this->entityManager->getClassMetadata(Project::class)->getTableName();

    /** @var array{total: string, visible: string, private_count: string, hidden: string, missing_files: string} $result */
    $result = $conn->fetchAssociative(sprintf(
      'SELECT COUNT(*) AS total,
              SUM(visible = 1 AND auto_hidden = 0 AND private = 0) AS visible,
              SUM(private = 1) AS private_count,
              SUM(visible = 0 OR auto_hidden = 1) AS hidden,
              SUM(has_missing_files = 1) AS missing_files
       FROM %s',
      $table,
    ));

    return [
      'total' => (int) $result['total'],
      'visible' => (int) $result['visible'],
      'private' => (int) $result['private_count'],
      'hidden' => (int) $result['hidden'],
      'missing_files' => (int) $result['missing_files'],
    ];
  }

  public function getEmailBudgetLevel(?int $totalRemaining = null): string
  {
    if (null === $totalRemaining) {
      $remaining = $this->emailBudgetManager->getRemainingBudget();
      $totalRemaining = $remaining['total'];
    }

    return match (true) {
      $totalRemaining < self::EMAIL_LOW_THRESHOLD => 'low',
      $totalRemaining < self::EMAIL_MODERATE_THRESHOLD => 'moderate',
      default => 'ok',
    };
  }
}
