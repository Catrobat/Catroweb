<?php

declare(strict_types=1);

namespace App\Docs;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

/** Run by every deploy and by the docs publish workflow (docs/operations/User-Docs.md). */
#[AsCommand(name: 'catrobat:docs:sync', description: 'Import the user docs (user-docs/) into the database')]
class DocsSyncCommand extends Command
{
  public function __construct(
    private readonly DocsSync $sync,
    private readonly LockFactory $locks,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addArgument('source', InputArgument::OPTIONAL, 'Another docs folder to import (default: catrobat.user_docs.dir)');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $source = $input->getArgument('source');
    $dir = \is_string($source) ? $source : $this->sync->docs_dir;
    if (!is_dir($dir)) {
      $output->writeln('No user docs at '.$dir.', nothing to sync.');

      return Command::SUCCESS;
    }
    // One sync at a time, e.g. a deploy and the publish workflow.
    $lock = $this->locks->createLock('docs-sync', 1800);
    if (!$lock->acquire()) {
      $output->writeln('A docs sync is already running.');

      return Command::SUCCESS;
    }
    try {
      $result = $this->sync->import(DocsSync::readDirectory($dir));
    } finally {
      $lock->release();
    }

    $output->writeln(\sprintf('Docs: %d created, %d updated, %d deleted.', $result['created'], $result['updated'], $result['deleted']));
    foreach ($result['skipped'] as $problem) {
      $output->writeln('Skipped '.$problem);
    }

    return Command::SUCCESS;
  }
}
