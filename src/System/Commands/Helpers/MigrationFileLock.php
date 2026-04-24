<?php

declare(strict_types=1);

namespace App\System\Commands\Helpers;

use Symfony\Component\Console\Output\OutputInterface;

class MigrationFileLock
{
  /** @var resource|closed-resource|null */
  private $lock_file;

  public function __construct(private readonly string $lock_file_path, private readonly OutputInterface $output)
  {
  }

  public function lock(): void
  {
    $file = fopen($this->lock_file_path, 'w+');
    if (false === $file) {
      throw new \RuntimeException('Could not open lock file: '.$this->lock_file_path);
    }

    $this->lock_file = $file;
    $this->output->writeln('[MigrationFileLock] Trying to acquire lock...');
    while (false === flock($this->lock_file, LOCK_EX)) {
      $this->output->writeln('[MigrationFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[MigrationFileLock] Lock acquired...');
    fwrite($this->lock_file, 'Migration of remixes in progress...');
  }

  public function unlock(): void
  {
    if (null === $this->lock_file) {
      return;
    }

    $lock_file = $this->lock_file;
    $this->lock_file = null;

    if (!is_resource($lock_file)) {
      return;
    }

    $this->output->writeln('[MigrationFileLock] Lock released...');
    flock($lock_file, LOCK_UN);
    fclose($lock_file);
    @unlink($this->lock_file_path);
  }
}
