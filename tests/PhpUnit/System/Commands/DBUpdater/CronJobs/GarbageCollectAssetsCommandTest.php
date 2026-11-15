<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\ProjectDeduplicationService;
use App\System\Commands\DBUpdater\CronJobs\GarbageCollectAssetsCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(GarbageCollectAssetsCommand::class)]
final class GarbageCollectAssetsCommandTest extends TestCase
{
  public function testADryRunCountsWithoutDeleting(): void
  {
    $service = $this->createMock(ProjectDeduplicationService::class);
    $service->expects($this->never())->method('garbageCollect');
    $assets = $this->createStub(ProjectAssetRepository::class);
    $assets->method('countOrphans')->willReturn(['count' => 112907, 'bytes' => 12_600_000_000]);

    $tester = new CommandTester(new GarbageCollectAssetsCommand($service, $assets, $this->createStub(LoggerInterface::class)));
    $tester->execute(['--dry-run' => true]);

    $this->assertStringContainsString('112907 orphaned assets (12600.0 MB) would be deleted', $tester->getDisplay());
  }

  public function testARunLogsOneSummaryLine(): void
  {
    $service = $this->createStub(ProjectDeduplicationService::class);
    $service->method('garbageCollect')->willReturn(['count' => 3, 'bytes' => 300]);
    $assets = $this->createStub(ProjectAssetRepository::class);
    $assets->method('countOrphans')->willReturn(['count' => 0, 'bytes' => 0]);
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('info')->with('cleanup_run', ['rule' => 'orphaned_assets', 'deleted' => 3, 'bytes' => 300, 'remaining' => 0]);

    new CommandTester(new GarbageCollectAssetsCommand($service, $assets, $logger))->execute([]);
  }
}
