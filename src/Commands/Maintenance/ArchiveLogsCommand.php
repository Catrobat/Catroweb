<?php

namespace App\Commands\Maintenance;

use App\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ArchiveLogsCommand extends Command
{
  private OutputInterface $output;

  private ParameterBagInterface $parameter_bag;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:logs:archive')
      ->setDescription('Archive the log files')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;

    $this->output->writeln('Archiving log files');
    $log_dir = $this->parameter_bag->get('catrobat.logs.dir');
    $old_log_dir = $log_dir.'old_logs';
    $compression_command = 'tar -zcvf '.$old_log_dir.'/log_backup_'.date('Y-m-d_His').'.tar.gz -C '.$log_dir;
    if (!file_exists($old_log_dir) && !is_dir($old_log_dir))
    {
      mkdir($old_log_dir);
    }
    CommandHelper::executeShellCommand(
      [
        'tar', '--absolute-names', '--exclude=old_logs', '-czvf', $old_log_dir.'/log_backup_'.date('Y-m-d_His').'.tar.gz', $log_dir,
      ],
      ['timeout' => 7200], 'Executing command: '.$compression_command.$log_dir, $output
    );
    $this->output->writeln('Successfully archived log files');

    return 0;
  }
}
