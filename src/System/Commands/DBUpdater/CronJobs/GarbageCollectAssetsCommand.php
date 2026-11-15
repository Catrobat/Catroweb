<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\ProjectDeduplicationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catrobat:gc-assets',
  description: 'Garbage-collect orphaned content-addressable assets',
)]
class GarbageCollectAssetsCommand extends Command
{
  /** An upload maps its assets within seconds; a day leaves room for any slow or retried one. */
  public const string GRACE_PERIOD = '-1 day';

  public function __construct(
    private readonly ProjectDeduplicationService $deduplicationService,
    private readonly ProjectAssetRepository $assetRepository,
    private readonly LoggerInterface $cleanupLogger,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count the orphaned assets and their size')
      ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Assets listed per query', '500')
      ->addOption('time-limit', null, InputOption::VALUE_REQUIRED, 'Stop starting new batches after this many seconds', '3600')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $created_before = new \DateTimeImmutable(self::GRACE_PERIOD);

    if ($input->getOption('dry-run')) {
      $orphans = $this->assetRepository->countOrphans($created_before);
      $io->success(sprintf('Dry run: %d orphaned assets (%s) would be deleted.', $orphans['count'], self::megabytes($orphans['bytes'])));

      return Command::SUCCESS;
    }

    $deadline = microtime(true) + (int) $input->getOption('time-limit');
    $deleted = $this->deduplicationService->garbageCollect($created_before, max(1, (int) $input->getOption('batch-size')), $deadline);
    $remaining = $this->assetRepository->countOrphans($created_before)['count'];

    $this->cleanupLogger->info('cleanup_run', ['rule' => 'orphaned_assets', 'deleted' => $deleted['count'], 'bytes' => $deleted['bytes'], 'remaining' => $remaining]);
    $io->success(sprintf('Garbage collected %d orphaned assets (%s); %d remain.', $deleted['count'], self::megabytes($deleted['bytes']), $remaining));

    return Command::SUCCESS;
  }

  private static function megabytes(int $bytes): string
  {
    return sprintf('%.1f MB', $bytes / 1_000_000);
  }
}
