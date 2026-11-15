<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\System\Cron\CronTaskRunner;
use App\System\Cron\CronTasks;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The scheduler workers run the periodic tasks (docs/operations/Background-Jobs.md). This runs them by hand: all due
 * tasks of both lanes, or one task by name whether due or not.
 */
#[AsCommand(name: 'catrobat:cronjob', description: 'Run due periodic tasks now, or one task by name')]
class CronJobCommand extends Command
{
  public function __construct(private readonly CronTaskRunner $runner)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addArgument('task', InputArgument::OPTIONAL, 'Name of one task to run even if it is not due');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $name = $input->getArgument('task');
    if (\is_string($name)) {
      $task = CronTasks::find($name);
      if (null === $task) {
        $output->writeln('<error>No task named "'.$name.'"</error>');

        return Command::INVALID;
      }

      $this->runner->run($task);

      return Command::SUCCESS;
    }

    $this->runner->runDue(false);
    $this->runner->runDue(true);

    return Command::SUCCESS;
  }
}
