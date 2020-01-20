<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Class ArchiveLogsCommand
 * @package App\Catrobat\Commands
 */
class ArchiveLogsCommand extends Command
{
  /**
   * @var
   */
  private $output;

  /**
   * @var ParameterBagInterface $parameter_bag
   */
  private $parameter_bag;

  /**
   * ArchiveLogsCommand constructor.
   *
   * @param ParameterBagInterface $parameter_bag
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }

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
    $log_dir = $this->parameter_bag->get('catrobat.logs.dir');
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