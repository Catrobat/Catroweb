<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\System\Commands\SendEmailDigestCommand;
use App\User\Notification\EmailDigestService;
use App\User\Notification\EmailNotificationPreference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(SendEmailDigestCommand::class)]
class SendEmailDigestCommandTest extends TestCase
{
  public function testExecuteDailyDigest(): void
  {
    $service = $this->createMock(EmailDigestService::class);
    $service
      ->expects($this->once())
      ->method('sendDigests')
      ->with(EmailNotificationPreference::DAILY)
      ->willReturn(5)
    ;

    $command = new SendEmailDigestCommand($service);
    $tester = new CommandTester($command);
    $tester->execute(['--period' => 'daily']);

    $this->assertSame(0, $tester->getStatusCode());
    $this->assertStringContainsString('5 daily', $tester->getDisplay());
  }

  public function testExecuteWeeklyDigest(): void
  {
    $service = $this->createMock(EmailDigestService::class);
    $service
      ->expects($this->once())
      ->method('sendDigests')
      ->with(EmailNotificationPreference::WEEKLY)
      ->willReturn(3)
    ;

    $command = new SendEmailDigestCommand($service);
    $tester = new CommandTester($command);
    $tester->execute(['--period' => 'weekly']);

    $this->assertSame(0, $tester->getStatusCode());
    $this->assertStringContainsString('3 weekly', $tester->getDisplay());
  }

  public function testExecuteImmediateDigest(): void
  {
    $service = $this->createMock(EmailDigestService::class);
    $service
      ->expects($this->once())
      ->method('sendDigests')
      ->with(EmailNotificationPreference::IMMEDIATE)
      ->willReturn(10)
    ;

    $command = new SendEmailDigestCommand($service);
    $tester = new CommandTester($command);
    $tester->execute(['--period' => 'immediate']);

    $this->assertSame(0, $tester->getStatusCode());
    $this->assertStringContainsString('10 immediate', $tester->getDisplay());
  }

  public function testExecuteInvalidPeriod(): void
  {
    $service = $this->createStub(EmailDigestService::class);

    $command = new SendEmailDigestCommand($service);
    $tester = new CommandTester($command);
    $tester->execute(['--period' => 'invalid']);

    $this->assertSame(1, $tester->getStatusCode());
    $this->assertStringContainsString('Invalid period', $tester->getDisplay());
  }

  public function testDefaultPeriodIsImmediate(): void
  {
    $service = $this->createMock(EmailDigestService::class);
    $service
      ->expects($this->once())
      ->method('sendDigests')
      ->with(EmailNotificationPreference::IMMEDIATE)
      ->willReturn(0)
    ;

    $command = new SendEmailDigestCommand($service);
    $tester = new CommandTester($command);
    $tester->execute([]);

    $this->assertSame(0, $tester->getStatusCode());
  }
}
