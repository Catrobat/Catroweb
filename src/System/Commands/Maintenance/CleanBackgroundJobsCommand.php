<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\EntityRepository\System\BackgroundJobRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** Clients poll a job for minutes; finished ones are kept a week for support questions, then deleted. */
#[AsCommand(name: 'catrobat:background-jobs:clean', description: 'End stale background jobs, delete finished ones older than a week')]
class CleanBackgroundJobsCommand extends Command
{
  public function __construct(private readonly BackgroundJobRepository $jobs)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln(sprintf('Failed %d stale background jobs.', $this->jobs->failStale()));
    $output->writeln(sprintf('Deleted %d finished background jobs.', $this->jobs->deleteFinishedBefore(new \DateTimeImmutable('-7 days'))));

    return Command::SUCCESS;
  }
}
