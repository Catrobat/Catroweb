<?php

declare(strict_types=1);

namespace App\System\Commands\Helpers;

use Symfony\Component\Console\Output\OutputInterface;

class MigrationFileLock
{
  private readonly string $lock_file_path;

  private mixed $lock_file;

  public function __construct(string $filePath, private readonly OutputInterface $output)
  {
    $this->lock_file_path = $filePath;
  }

  public function lock(): void
  {
    $this->lock_file = fopen($this->lock_file_path, 'w+');
    $this->output->writeln('[MigrationFileLock] Trying to acquire lock...');
    while (false == flock($this->lock_file, LOCK_EX)) {
      $this->output->writeln('[MigrationFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[MigrationFileLock] Lock acquired...');
    fwrite($this->lock_file, 'Migration of remixes in progress...');
  }

  public function unlock(): void
  {
    if (null == $this->lock_file) {
      return;
    }

    $this->output->writeln('[MigrationFileLock] Lock released...');
    flock($this->lock_file, LOCK_UN);
    fclose($this->lock_file);
    @unlink($this->lock_file_path);
  }
}
