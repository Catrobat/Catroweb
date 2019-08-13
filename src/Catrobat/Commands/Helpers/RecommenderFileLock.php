<?php

namespace App\Catrobat\Commands\Helpers;

use App\Catrobat\RecommenderSystem\RecommenderManager;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class RecommenderFileLock
 * @package App\Catrobat\Commands
 */
class RecommenderFileLock
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
   * RecommenderFileLock constructor.
   *
   * @param                 $app_root_dir
   * @param OutputInterface $output
   */
  public function __construct($app_root_dir, OutputInterface $output)
  {
    $this->lock_file_path = $app_root_dir . '/' . RecommenderManager::RECOMMENDER_LOCK_FILE_NAME;
    $this->lock_file = null;
    $this->output = $output;
  }

  /**
   *
   */
  public function lock()
  {
    $this->lock_file = fopen($this->lock_file_path, 'w+');
    $this->output->writeln('[RecommenderFileLock] Trying to acquire lock...');
    while (flock($this->lock_file, LOCK_EX) == false)
    {
      $this->output->writeln('[RecommenderFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[RecommenderFileLock] Lock acquired...');
    fwrite($this->lock_file, 'User similarity computation in progress...');
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

    $this->output->writeln('[RecommenderFileLock] Lock released...');
    flock($this->lock_file, LOCK_UN);
    fclose($this->lock_file);
    @unlink($this->lock_file_path);
  }
}