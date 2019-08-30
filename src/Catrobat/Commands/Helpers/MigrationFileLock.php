<?php

namespace App\Catrobat\Commands\Helpers;

use App\Catrobat\Listeners\RemixUpdater;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class MigrationFileLock
 * @package App\Catrobat\Commands
 */
class MigrationFileLock
{
  /**
   * @var string
   */
  private $lock_file_path;
  /**
   * @var null
   */
  private $lock_file;
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * MigrationFileLock constructor.
   *
   * @param                 $app_root_dir
   * @param OutputInterface $output
   */
  public function __construct($app_root_dir, OutputInterface $output)
  {
    $this->lock_file_path = $app_root_dir . '/' . RemixUpdater::MIGRATION_LOCK_FILE_NAME;
    $this->lock_file = null;
    $this->output = $output;
  }

  /**
   *
   */
  public function lock()
  {
    $this->lock_file = fopen($this->lock_file_path, 'w+');
    $this->output->writeln('[MigrationFileLock] Trying to acquire lock...');
    while (flock($this->lock_file, LOCK_EX) == false)
    {
      $this->output->writeln('[MigrationFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[MigrationFileLock] Lock acquired...');
    fwrite($this->lock_file, 'Migration of remixes in progress...');
  }

  /**
   *
   */
  public function unlock()
  {
    if ($this->lock_file == null)
    {
      return;
    }

    $this->output->writeln('[MigrationFileLock] Lock released...');
    flock($this->lock_file, LOCK_UN);
    fclose($this->lock_file);
    @unlink($this->lock_file_path);
  }
}