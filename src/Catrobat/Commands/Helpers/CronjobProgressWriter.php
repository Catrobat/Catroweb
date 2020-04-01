<?php

namespace App\Catrobat\Commands\Helpers;

use App\Utils\TimeUtils;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class CronjobProgressWriter //extends ProgressBar
{
  private OutputInterface $output;

  public function __construct(OutputInterface $output)
  {
    $this->output = $output;
  }

  public function clear(): void
  {
  }

  public function advance(int $step = 1): void
  {
  }

  public function display(): void
  {
  }

  /**
   * @param null $max
   */
  public function start($max = null): void
  {
  }

  public function finish(): void
  {
  }

  /**
   * @param mixed $format
   */
  public function setFormat($format): void
  {
  }

  /**
   * @throws Exception
   */
  public function setMessage(string $message): void
  {
    $this->output->writeln('['.date_format(TimeUtils::getDateTime(), 'Y-m-d H:i:s').'] '.$message);
  }
}
