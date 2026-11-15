<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\GarbageCollectOrphanedAssets;
use App\Project\GarbageCollectOrphanedAssetsHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Starts the batch chain on the "cleanup" queue (GarbageCollectOrphanedAssetsHandler), unless one is still queued.
 */
#[AsCommand(
  name: 'catrobat:gc-assets',
  description: 'Garbage-collect orphaned content-addressable assets',
)]
class GarbageCollectAssetsCommand extends Command
{
  public function __construct(
    private readonly ProjectAssetRepository $assetRepository,
    private readonly MessageBusInterface $bus,
    #[Autowire(service: 'messenger.transport.cleanup')]
    private readonly TransportInterface $cleanupQueue,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count the orphaned assets and their size');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $orphans = $this->assetRepository->countOrphans(new \DateTimeImmutable(GarbageCollectOrphanedAssetsHandler::GRACE_PERIOD));
    $summary = sprintf('%d orphaned assets (%.1f MB)', $orphans['count'], $orphans['bytes'] / 1_000_000);

    if ($input->getOption('dry-run')) {
      $io->success("Dry run: {$summary} would be deleted.");

      return Command::SUCCESS;
    }

    // ponytail: a batch in progress is not counted, so a start during it begins a second chain. Harmless: the
    // chains delete the same rows, a delete counts only once, and both end at the last orphan.
    if ($this->cleanupQueue instanceof MessageCountAwareInterface && $this->cleanupQueue->getMessageCount() > 0) {
      $io->note("Garbage collection is already queued; {$summary} left.");

      return Command::SUCCESS;
    }

    if (0 === $orphans['count']) {
      $io->success('No orphaned assets.');

      return Command::SUCCESS;
    }

    $this->bus->dispatch(new GarbageCollectOrphanedAssets());
    $io->success("Queued garbage collection of {$summary}.");

    return Command::SUCCESS;
  }
}
