<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\Project\ProjectDeduplicationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catrobat:gc-assets',
  description: 'Garbage-collect orphaned content-addressable assets',
)]
class GarbageCollectAssetsCommand extends Command
{
  public function __construct(
    private readonly ProjectDeduplicationService $deduplicationService,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max assets to delete per run', '500');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $limit = (int) $input->getOption('limit');

    $deleted = $this->deduplicationService->garbageCollect($limit);
    $io->success("Garbage collected {$deleted} orphaned assets.");

    return Command::SUCCESS;
  }
}
