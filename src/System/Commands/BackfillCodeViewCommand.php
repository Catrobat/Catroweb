<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\Project\CodeView\CodeExportUnavailableException;
use App\Project\CodeView\CodeViewExportStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

/**
 * Makes the code views that are missing or older than the deployed code export, e.g. after a player update, so
 * viewers do not wait for them. Without ids it takes the projects whose files are extracted: the ones viewed or
 * downloaded lately; the others get theirs when they are first viewed. Safe to run alongside production traffic.
 */
#[AsCommand(
  name: 'catro:backfill:code-view',
  description: 'Export the code view of projects whose export is missing or outdated.',
)]
class BackfillCodeViewCommand extends Command
{
  public function __construct(
    private readonly CodeViewExportStore $store,
    private readonly ProjectRepository $project_repository,
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extract_dir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('ids', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Project ids (default: all extracted projects)')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Process at most N projects (0 = unlimited).', '0')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $limit = max(0, (int) $input->getOption('limit'));
    /** @var list<string> $ids */
    $ids = $input->getArgument('ids');
    if ([] === $ids && is_dir($this->extract_dir)) {
      foreach ((new Finder())->directories()->in($this->extract_dir)->depth('== 0')->sortByModifiedTime()->reverseSorting() as $dir) {
        $ids[] = $dir->getFilename();
      }
    }
    if ($limit > 0) {
      $ids = array_slice($ids, 0, $limit);
    }

    $shown = 0;
    $failed = 0;
    $progress = $io->createProgressBar(count($ids));
    foreach ($ids as $id) {
      $progress->advance();
      $project = $this->project_repository->find($id);
      if (!$project instanceof Project) {
        continue;
      }
      try {
        $export = $this->store->refresh($project, true);
      } catch (CodeExportUnavailableException) {
        $export = null;
      }
      null === $export || isset($export['error']) ? ++$failed : ++$shown;
    }
    $progress->finish();
    $io->newLine(2);
    $io->success(sprintf('%d code view(s) up to date, %d project(s) without one.', $shown, $failed));

    return Command::SUCCESS;
  }
}
