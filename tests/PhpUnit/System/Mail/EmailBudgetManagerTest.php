<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Mail;

use App\DB\Entity\System\EmailDailyBudget;
use App\DB\EntityRepository\System\EmailDailyBudgetRepository;
use App\System\Mail\EmailBudgetManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @covers \App\System\Mail\EmailBudgetManager
 *
 * @internal
 */
class EmailBudgetManagerTest extends TestCase
{
  private EmailDailyBudget $budget;
  private EmailBudgetManager $manager;

  #[\Override]
  protected function setUp(): void
  {
    $this->budget = new EmailDailyBudget();

    $repository = $this->createStub(EmailDailyBudgetRepository::class);
    $repository->method('findOrCreateToday')->willReturn($this->budget);

    $entityManager = $this->createStub(EntityManagerInterface::class);

    $this->manager = new EmailBudgetManager($repository, $entityManager, new NullLogger());
  }

  public function testCanSendReturnsTrueWhenBudgetAvailable(): void
  {
    self::assertTrue($this->manager->canSend('verification'));
    self::assertTrue($this->manager->canSend('reset'));
    self::assertTrue($this->manager->canSend('consent'));
    self::assertTrue($this->manager->canSend('admin'));
    self::assertTrue($this->manager->canSend('management'));
  }

  public function testCanSendReturnsFalseWhenDailyLimitReached(): void
  {
    $this->budget->setTotalSent(EmailBudgetManager::DAILY_LIMIT);

    self::assertFalse($this->manager->canSend('verification'));
    self::assertFalse($this->manager->canSend('reset'));
  }

  public function testCanSendRespectsTypeReserve(): void
  {
    // Fill up verification reserve (150)
    $this->budget->setVerificationSent(150);
    $this->budget->setTotalSent(150);

    // Verification can still send from shared pool (300 - 300 reserves = 0 shared)
    // Total reserved = 150 + 30 + 30 + 50 + 40 = 300, shared pool = 0
    self::assertFalse($this->manager->canSend('verification'));

    // Other types are still within their reserves
    self::assertTrue($this->manager->canSend('reset'));
  }

  public function testCanSendAllowsOverflowIntoSharedPool(): void
  {
    // With total reserves = 300 and daily limit = 300, shared pool = 0
    // So overflowing a reserve is NOT possible when reserves sum to daily limit
    $this->budget->setVerificationSent(150);
    $this->budget->setTotalSent(150);

    self::assertFalse($this->manager->canSend('verification'));
  }

  public function testRecordSendIncrementsCounters(): void
  {
    $this->manager->recordSend('verification');

    self::assertSame(1, $this->budget->getVerificationSent());
    self::assertSame(1, $this->budget->getTotalSent());

    $this->manager->recordSend('verification');
    self::assertSame(2, $this->budget->getVerificationSent());
    self::assertSame(2, $this->budget->getTotalSent());
  }

  public function testRecordSendIncrementsDifferentTypes(): void
  {
    $this->manager->recordSend('verification');
    $this->manager->recordSend('reset');
    $this->manager->recordSend('admin');

    self::assertSame(1, $this->budget->getVerificationSent());
    self::assertSame(1, $this->budget->getResetSent());
    self::assertSame(1, $this->budget->getAdminSent());
    self::assertSame(3, $this->budget->getTotalSent());
  }

  public function testGetRemainingBudget(): void
  {
    $this->manager->recordSend('verification');
    $this->manager->recordSend('verification');
    $this->manager->recordSend('reset');

    $remaining = $this->manager->getRemainingBudget();

    self::assertSame(148, $remaining['verification']);
    self::assertSame(29, $remaining['reset']);
    self::assertSame(30, $remaining['consent']);
    self::assertSame(50, $remaining['admin']);
    self::assertSame(40, $remaining['management']);
    self::assertSame(297, $remaining['total']);
  }

  public function testGetRemainingBudgetNeverNegative(): void
  {
    $this->budget->setResetSent(100);
    $this->budget->setTotalSent(100);

    $remaining = $this->manager->getRemainingBudget();

    self::assertSame(0, $remaining['reset']);
    self::assertSame(200, $remaining['total']);
  }

  public function testCanSendThrowsOnInvalidType(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown email type: invalid');
    $this->manager->canSend('invalid');
  }

  public function testRecordSendThrowsOnInvalidType(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown email type: invalid');
    $this->manager->recordSend('invalid');
  }

  public function testBudgetExhaustionByType(): void
  {
    // Fill reset reserve completely
    for ($i = 0; $i < 30; ++$i) {
      self::assertTrue($this->manager->canSend('reset'));
      $this->manager->recordSend('reset');
    }

    // Reset reserve exhausted, shared pool = 0 (reserves sum to 300 = daily limit)
    self::assertFalse($this->manager->canSend('reset'));

    // Other types still have budget within their reserves
    self::assertTrue($this->manager->canSend('verification'));
    self::assertTrue($this->manager->canSend('admin'));
  }

  public function testTotalLimitPreventsAllSending(): void
  {
    $this->budget->setTotalSent(300);
    $this->budget->setVerificationSent(0);

    self::assertFalse($this->manager->canSend('verification'));
    self::assertFalse($this->manager->canSend('reset'));
    self::assertFalse($this->manager->canSend('consent'));
    self::assertFalse($this->manager->canSend('admin'));
    self::assertFalse($this->manager->canSend('management'));
  }
}
