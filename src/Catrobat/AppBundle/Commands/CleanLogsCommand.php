<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class CleanLogsCommand
 * @package Catrobat\AppBundle\Commands
 */
class CleanLogsCommand extends ContainerAwareCommand
{
  /**
   * @var
   */
  private $output;

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:logs:clean')
      ->setDescription('Delete the log files');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $this->output->writeln('Deleting log files');
    $log_dir = $this->getContainer()->getParameter('catrobat.logs.dir');
    $log_files = glob($log_dir . "*.log");
    foreach ($log_files as $log_file)
    {
      unlink($log_file);
    }
    $this->output->writeln('Successfully deleted log files');
  }
} 