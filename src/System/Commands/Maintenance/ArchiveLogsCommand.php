<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\System\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:logs:archive', description: 'Archive the log files')]
class ArchiveLogsCommand extends Command
{
  public function __construct(private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output1 = $output;

    $output1->writeln('Archiving log files');
    $log_dir = $this->parameter_bag->get('catrobat.logs.dir');
    $old_log_dir = $log_dir.'old_logs';
    $compression_command = 'tar -zcvf '.$old_log_dir.'/log_backup_'.date('Y-m-d_His').'.tar.gz -C '.$log_dir;
    if (!file_exists($old_log_dir) && !is_dir($old_log_dir)) {
      mkdir($old_log_dir);
    }
    CommandHelper::executeShellCommand(
      [
        'tar', '--absolute-names', '--exclude=old_logs', '-czvf', $old_log_dir.'/log_backup_'.date('Y-m-d_His').'.tar.gz', $log_dir,
      ],
      ['timeout' => 7200], 'Executing command: '.$compression_command.$log_dir, $output
    );
    $output1->writeln('Successfully archived log files');

    return 0;
  }
}
