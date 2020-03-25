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

  public function clear()
  {
  }

  public function advance(int $step = 1)
  {
  }

  public function display()
  {
  }

  /**
   * @param null $max
   */
  public function start($max = null)
  {
  }

  public function finish()
  {
  }

  /**
   * @param $format
   */
  public function setFormat($format)
  {
  }

  /**
   * @throws Exception
   */
  public function setMessage(string $message)
  {
    $this->output->writeln('['.date_format(TimeUtils::getDateTime(), 'Y-m-d H:i:s').'] '.$message);
  }
}
