<?php

namespace App\Catrobat\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Commands\Helpers\CommandHelper;


/**
 * Class ArchiveLogsCommand
 * @package App\Catrobat\Commands
 */
class ArchiveLogsCommand extends ContainerAwareCommand
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
    $this->setName('catrobat:logs:archive')
      ->setDescription('Archive the log files');
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

    $this->output->writeln('Archiving log files');
    $log_dir = $this->getContainer()->getParameter('catrobat.logs.dir');
    $old_log_dir = $log_dir . "old_logs";
    $compression_command = "tar -zcvf " . $old_log_dir . "/log_backup_" . date('Y-m-d_His') . '.tar.gz -C '
      . $log_dir;
    if (!file_exists($old_log_dir) && !is_dir($old_log_dir))
    {
      mkdir($old_log_dir);
    }
    $log_files = glob($log_dir . "*.log");
    $affected_files = '';
    foreach ($log_files as $log_file)
    {
      $affected_files .= ' ' . basename($log_file);
    }
    CommandHelper::executeShellCommand($compression_command . $affected_files,
      ['timeout' => 7200], "Executing command: " . $compression_command . $affected_files, $output);
    $this->output->writeln('Successfully archived log files');
  }
} 