<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\System\Maintenance;

use App\Admin\System\Maintenance\SystemHealthService;
use App\DB\Entity\System\EmailDailyBudget;
use App\DB\EntityRepository\System\EmailDailyBudgetRepository;
use App\System\Mail\EmailBudgetManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @covers \App\Admin\System\Maintenance\SystemHealthService
 *
 * @internal
 */
class SystemHealthServiceTest extends TestCase
{
  private SystemHealthService $service;

  #[\Override]
  protected function setUp(): void
  {
    $budget = new EmailDailyBudget();
    $budget->setTotalSent(25);
    $budget->setVerificationSent(10);
    $budget->setResetSent(5);
    $budget->setConsentSent(3);
    $budget->setAdminSent(4);
    $budget->setManagementSent(3);

    $repository = $this->createStub(EmailDailyBudgetRepository::class);
    $repository->method('findOrCreateToday')->willReturn($budget);

    $entityManagerForBudget = $this->createStub(EntityManagerInterface::class);
    $emailBudgetManager = new EmailBudgetManager($repository, $entityManagerForBudget, new NullLogger());

    $connection = $this->createStub(Connection::class);
    $connection->method('fetchAssociative')->willReturn([
      'total' => '100',
      'visible' => '80',
      'private_count' => '10',
      'hidden' => '15',
    ]);

    $metadata = $this->createStub(ClassMetadata::class);
    $metadata->method('getTableName')->willReturn('project');

    $entityManager = $this->createStub(EntityManagerInterface::class);
    $entityManager->method('getConnection')->willReturn($connection);
    $entityManager->method('getClassMetadata')->willReturn($metadata);

    $this->service = new SystemHealthService(
      $entityManager,
      $emailBudgetManager,
    );
  }

  public function testGetEmailBudgetReturnsCorrectStructure(): void
  {
    $email = $this->service->getEmailBudget();

    self::assertSame(300, $email['daily_limit']);
    self::assertSame(25, $email['sent_today']);
    self::assertSame(275, $email['remaining']);
    self::assertArrayHasKey('breakdown', $email);
    self::assertArrayHasKey('verification', $email['breakdown']);
    self::assertArrayHasKey('reset', $email['breakdown']);
    self::assertSame(10, $email['breakdown']['verification']['sent']);
    self::assertSame(150, $email['breakdown']['verification']['reserve']);
    self::assertSame(140, $email['breakdown']['verification']['remaining']);
  }

  public function testGetProjectCountsReturnsAllCategories(): void
  {
    $projects = $this->service->getProjectCounts();

    self::assertArrayHasKey('total', $projects);
    self::assertArrayHasKey('visible', $projects);
    self::assertArrayHasKey('private', $projects);
    self::assertArrayHasKey('hidden', $projects);
    self::assertSame(100, $projects['total']);
    self::assertSame(80, $projects['visible']);
    self::assertSame(10, $projects['private']);
    self::assertSame(15, $projects['hidden']);
  }

  #[DataProvider('storagePressureProvider')]
  public function testGetStoragePressureLevel(float $percentage, int $freeSpace, string $expected): void
  {
    self::assertSame($expected, $this->service->getStoragePressureLevel($percentage, $freeSpace));
  }

  /**
   * @return array<string, array{float, int, string}>
   */
  public static function storagePressureProvider(): array
  {
    return [
      'normal - low usage' => [50.0, 50_000_000_000, 'normal'],
      'normal - at warning boundary minus one' => [79.9, 11_000_000_000, 'normal'],
      'warning - high percentage' => [80.0, 50_000_000_000, 'warning'],
      'warning - low free space' => [50.0, 10_737_418_240 - 1, 'warning'],
      'warning - at critical boundary minus one' => [89.9, 6_000_000_000, 'warning'],
      'critical - high percentage' => [90.0, 50_000_000_000, 'critical'],
      'critical - low free space' => [50.0, 5_368_709_120 - 1, 'critical'],
      'critical - at emergency boundary minus one' => [94.9, 2_000_000_000, 'critical'],
      'emergency - high percentage' => [95.0, 50_000_000_000, 'emergency'],
      'emergency - very low free space' => [50.0, 1_073_741_824 - 1, 'emergency'],
      'emergency - both triggers' => [96.0, 500_000_000, 'emergency'],
      'warning - percentage ok but free space triggers' => [70.0, 8_000_000_000, 'warning'],
      'critical - percentage ok but free space triggers' => [70.0, 3_000_000_000, 'critical'],
    ];
  }

  #[DataProvider('emailBudgetLevelProvider')]
  public function testGetEmailBudgetLevel(int $totalRemaining, string $expected): void
  {
    $budget = new EmailDailyBudget();
    $totalSent = EmailBudgetManager::DAILY_LIMIT - $totalRemaining;
    $budget->setTotalSent($totalSent);
    $budget->setVerificationSent(0);
    $budget->setResetSent(0);
    $budget->setConsentSent(0);
    $budget->setAdminSent(0);
    $budget->setManagementSent(0);

    $repository = $this->createStub(EmailDailyBudgetRepository::class);
    $repository->method('findOrCreateToday')->willReturn($budget);

    $entityManagerForBudget = $this->createStub(EntityManagerInterface::class);
    $emailBudgetManager = new EmailBudgetManager($repository, $entityManagerForBudget, new NullLogger());

    $entityManager = $this->createStub(EntityManagerInterface::class);

    $service = new SystemHealthService($entityManager, $emailBudgetManager);

    self::assertSame($expected, $service->getEmailBudgetLevel());
  }

  /**
   * @return array<string, array{int, string}>
   */
  public static function emailBudgetLevelProvider(): array
  {
    return [
      'ok - high remaining' => [275, 'ok'],
      'ok - at moderate boundary' => [100, 'ok'],
      'moderate - below threshold' => [99, 'moderate'],
      'moderate - at low boundary' => [30, 'moderate'],
      'low - below threshold' => [29, 'low'],
      'low - zero remaining' => [0, 'low'],
    ];
  }
}
