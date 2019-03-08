<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class CronjobProgressWriter
 * @package App\Catrobat\Commands
 */
class CronjobProgressWriter #extends ProgressBar
{
  /**
   * @var OutputInterface
   */
  private $_output = null;

  /**
   * CronjobProgressWriter constructor.
   *
   * @param OutputInterface $output
   * @param int             $max
   */
  public function __construct(OutputInterface $output, $max = 0)
  {
    $this->_output = $output;
  }

  /**
   *
   */
  public function clear()
  {
  }

  /**
   * @param int $step
   */
  public function advance($step = 1)
  {
  }

  /**
   *
   */
  public function display()
  {
  }

  /**
   * @param null $max
   */
  public function start($max = null)
  {
  }

  /**
   *
   */
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
   * @param        $message
   * @param string $name
   *
   * @throws \Exception
   */
  public function setMessage($message, $name = 'message')
  {
    $this->_output->writeln('[' . date_format(new \DateTime(), "Y-m-d H:i:s") . '] ' . $message);
  }
}
