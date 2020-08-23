<?php

namespace App\Commands\Helpers;

use App\Catrobat\Listeners\RemixUpdater;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationFileLock
{
  private string $lock_file_path;

  /**
   * @var mixed
   */
  private $lock_file;

  private OutputInterface $output;

  public function __construct(string $app_root_dir, OutputInterface $output)
  {
    $this->lock_file_path = $app_root_dir.'/'.RemixUpdater::MIGRATION_LOCK_FILE_NAME;
    $this->lock_file = null;
    $this->output = $output;
  }

  public function lock(): void
  {
    $this->lock_file = fopen($this->lock_file_path, 'w+');
    $this->output->writeln('[MigrationFileLock] Trying to acquire lock...');
    while (false == flock($this->lock_file, LOCK_EX))
    {
      $this->output->writeln('[MigrationFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[MigrationFileLock] Lock acquired...');
    fwrite($this->lock_file, 'Migration of remixes in progress...');
  }

  public function unlock(): void
  {
    if (null == $this->lock_file)
    {
      return;
    }

    $this->output->writeln('[MigrationFileLock] Lock released...');
    flock($this->lock_file, LOCK_UN);
    fclose($this->lock_file);
    @unlink($this->lock_file_path);
  }
}
