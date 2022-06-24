<?php

namespace App\System\Commands;

use App\System\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreBackupCommand extends Command
{
  protected function configure(): void
  {
    $this
      ->setName('catrobat:backup:restore')
      ->setDescription('Restores a borg backup')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln('Restore backup on localhost');

    CommandHelper::executeShellCommand(
            ['bin/console', 'catrobat:purge', '--force'], [], 'Purging database', $output
        );

    CommandHelper::executeShellCommand(
            ['sh', 'bin/borg_restore_share.sh'], [86400],
            'Executing borg restore script [timeout = 24h]', $output
        );

    $output->writeln('Import finished!');

    return 0;
  }
}
