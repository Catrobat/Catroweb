<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\Storage\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'catrobat:clean:extracts', description: 'Delete extracted project directories older than N days')]
class CleanExtractsCommand extends Command
{
  private const int DEFAULT_DAYS = 7;

  private const int BATCH_SIZE = 100;

  public function __construct(
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extract_dir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Delete directories older than this many days', (string) self::DEFAULT_DAYS)
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview what would be deleted without actually deleting')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $days = (int) $input->getOption('days');
    if ($days < 1) {
      $output->writeln('<error>Days must be a positive integer.</error>');

      return Command::FAILURE;
    }

    $dry_run = (bool) $input->getOption('dry-run');

    if (!is_dir($this->extract_dir)) {
      $output->writeln('<error>Extract directory does not exist: '.$this->extract_dir.'</error>');

      return Command::FAILURE;
    }

    $cutoff = new \DateTimeImmutable('-'.$days.' days');
    $cutoff_timestamp = $cutoff->getTimestamp();

    $output->writeln(($dry_run ? '[DRY RUN] ' : '').'Cleaning extracted project directories older than '.$days.' days...');

    $finder = new Finder();
    $finder->in($this->extract_dir)->directories()->depth(0);

    $deleted_count = 0;
    $total_bytes_freed = 0;
    $batch_count = 0;

    foreach ($finder as $dir) {
      if ($dir->getMTime() >= $cutoff_timestamp) {
        continue;
      }

      $dir_size = $this->getDirectorySize($dir->getPathname());

      if ($dry_run) {
        $output->writeln('  Would delete: '.$dir->getFilename().' ('.self::formatBytes($dir_size).')');
      } else {
        try {
          FileHelper::removeDirectory($dir->getPathname());
          $output->writeln('  Deleted: '.$dir->getFilename().' ('.self::formatBytes($dir_size).')', OutputInterface::VERBOSITY_VERBOSE);
        } catch (\Exception $e) {
          $output->writeln('<error>  Failed to delete '.$dir->getFilename().': '.$e->getMessage().'</error>');
          continue;
        }
      }

      $total_bytes_freed += $dir_size;
      ++$deleted_count;
      ++$batch_count;

      if ($batch_count >= self::BATCH_SIZE) {
        $batch_count = 0;
        gc_collect_cycles();
      }
    }

    $output->writeln(($dry_run ? '[DRY RUN] ' : '').'Done. Directories deleted: '.$deleted_count.'. Disk space reclaimed: '.self::formatBytes($total_bytes_freed));

    return Command::SUCCESS;
  }

  private function getDirectorySize(string $path): int
  {
    $size = 0;

    $finder = new Finder();
    $finder->in($path)->files();

    foreach ($finder as $file) {
      $size += $file->getSize();
    }

    return $size;
  }

  private static function formatBytes(int $bytes): string
  {
    if ($bytes < 1024) {
      return $bytes.' B';
    }

    $units = ['KB', 'MB', 'GB'];
    $value = (float) $bytes;
    $unit_index = -1;

    while ($value >= 1024 && $unit_index < 2) {
      $value /= 1024;
      ++$unit_index;
    }

    return round($value, 2).' '.$units[$unit_index];
  }
}
