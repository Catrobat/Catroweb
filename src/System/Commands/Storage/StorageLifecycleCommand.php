<?php

declare(strict_types=1);

namespace App\System\Commands\Storage;

use App\Storage\StorageLifecycleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'catrobat:storage:lifecycle', description: 'Delete expired projects based on retention tier rules and disk pressure.')]
class StorageLifecycleCommand extends Command
{
  public function __construct(
    private readonly StorageLifecycleService $lifecycle_service,
    #[Autowire('%catrobat.file.storage.dir%')]
    private readonly string $storage_dir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be deleted without actually deleting');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');

    $io->title('Storage Lifecycle Manager');

    if ($dry_run) {
      $io->note('DRY-RUN mode: no projects will be deleted.');
    }

    $disk_ratio = $this->lifecycle_service->getDiskUsageRatio($this->storage_dir);
    $io->text(sprintf('Current disk usage: %.1f%%', $disk_ratio * 100.0));

    if ($this->lifecycle_service->shouldPauseUploads($disk_ratio)) {
      $io->warning('CRITICAL: Disk usage above 95% -- uploads should be paused!');
    }

    $result = $this->lifecycle_service->deleteExpiredProjects($dry_run, $this->storage_dir);

    $action = $dry_run ? 'would delete' : 'deleted';

    $io->success(sprintf(
      'Done. Checked %d projects: %s %d, %d errors.',
      $result['checked'],
      $action,
      $result['deleted'],
      $result['errors'],
    ));

    return Command::SUCCESS;
  }
}
