<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\GarbageCollectOrphanedAssets;
use App\System\Commands\DBUpdater\CronJobs\GarbageCollectAssetsCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @internal
 */
#[CoversClass(GarbageCollectAssetsCommand::class)]
final class GarbageCollectAssetsCommandTest extends TestCase
{
  public function testADryRunCountsWithoutQueueing(): void
  {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->never())->method('dispatch');

    $display = $this->runCommand(['--dry-run' => true], $bus, 0);

    $this->assertStringContainsString('112907 orphaned assets (12600.0 MB) would be deleted', $display);
  }

  public function testStartsTheChainWhenNoneIsQueued(): void
  {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->once())->method('dispatch')
      ->with($this->isInstanceOf(GarbageCollectOrphanedAssets::class))
      ->willReturnCallback(static fn (object $message): Envelope => new Envelope($message))
    ;

    $this->assertStringContainsString('Queued garbage collection', $this->runCommand([], $bus, 0));
  }

  public function testLeavesARunningChainAlone(): void
  {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->never())->method('dispatch');

    $this->assertStringContainsString('already queued', $this->runCommand([], $bus, 1));
  }

  /** @param array<string, bool> $options */
  private function runCommand(array $options, MessageBusInterface $bus, int $queued): string
  {
    $assets = $this->createStub(ProjectAssetRepository::class);
    $assets->method('countOrphans')->willReturn(['count' => 112907, 'bytes' => 12_600_000_000]);
    $queue = new class($queued) implements TransportInterface, MessageCountAwareInterface {
      public function __construct(private readonly int $queued)
      {
      }

      #[\Override]
      public function get(): iterable
      {
        return [];
      }

      #[\Override]
      public function ack(Envelope $envelope): void
      {
      }

      #[\Override]
      public function reject(Envelope $envelope): void
      {
      }

      #[\Override]
      public function send(Envelope $envelope): Envelope
      {
        return $envelope;
      }

      #[\Override]
      public function getMessageCount(): int
      {
        return $this->queued;
      }
    };
    $tester = new CommandTester(new GarbageCollectAssetsCommand($assets, $bus, $queue));
    $tester->execute($options);

    return $tester->getDisplay();
  }
}
