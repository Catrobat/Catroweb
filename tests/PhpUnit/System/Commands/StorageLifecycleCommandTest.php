<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\Storage\StorageLifecycleService;
use App\System\Commands\Storage\StorageLifecycleCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(StorageLifecycleCommand::class)]
final class StorageLifecycleCommandTest extends TestCase
{
  public function testOwnersAreWarnedBeforeExpiredProjectsAreDeleted(): void
  {
    $calls = [];
    $service = $this->createMock(StorageLifecycleService::class);
    $service->method('getDiskUsageRatio')->willReturn(0.1);
    $service->expects($this->once())->method('sendExpiryWarnings')->with(7, '/storage')
      ->willReturnCallback(static function () use (&$calls): array {
        $calls[] = 'warn';

        return ['warned' => 3];
      })
    ;
    $service->expects($this->once())->method('deleteExpiredProjects')->with(false, '/storage')
      ->willReturnCallback(static function () use (&$calls): array {
        $calls[] = 'delete';

        return ['checked' => 10, 'deleted' => 1, 'errors' => 0];
      })
    ;

    $tester = new CommandTester(new StorageLifecycleCommand($service, '/storage'));
    $tester->execute([]);

    $this->assertSame(['warn', 'delete'], $calls);
    $this->assertStringContainsString('Expiry warnings sent: 3', $tester->getDisplay());
  }

  public function testADryRunNeitherWarnsNorDeletes(): void
  {
    $service = $this->createMock(StorageLifecycleService::class);
    $service->method('getDiskUsageRatio')->willReturn(0.1);
    $service->expects($this->never())->method('sendExpiryWarnings');
    $service->expects($this->once())->method('deleteExpiredProjects')->with(true, '/storage')
      ->willReturn(['checked' => 10, 'deleted' => 2, 'errors' => 0])
    ;

    $tester = new CommandTester(new StorageLifecycleCommand($service, '/storage'));
    $tester->execute(['--dry-run' => true]);

    $this->assertStringContainsString('would delete 2', $tester->getDisplay());
  }
}
