<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Project;
use App\Project\ProjectDeduplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
  name: 'catrobat:migrate-assets',
  description: 'Migrate existing project assets to content-addressable store',
)]
class MigrateAssetsToContentAddressableStoreCommand extends Command
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly ProjectDeduplicationService $deduplicationService,
    private readonly LoggerInterface $logger,
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extractDir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Projects per batch', '50')
      ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Skip first N projects', '0')
      ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max projects to process (0 = all)', '0')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be done without doing it')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $batchSize = (int) $input->getOption('batch-size');
    $offset = (int) $input->getOption('offset');
    $limit = (int) $input->getOption('limit');
    $dryRun = (bool) $input->getOption('dry-run');

    $qb = $this->entityManager->createQueryBuilder()
      ->select('p.id')
      ->from(Project::class, 'p')
      ->orderBy('p.uploaded_at', 'ASC')
    ;

    $totalQuery = clone $qb;
    $total = $totalQuery->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

    $io->title('Migrating project assets to content-addressable store');
    $io->text("Total projects: {$total}");

    $processed = 0;
    $skipped = 0;
    $errors = 0;
    $currentOffset = $offset;

    while (true) {
      $ids = $qb->select('p.id')->setFirstResult($currentOffset)
        ->setMaxResults($batchSize)
        ->getQuery()
        ->getSingleColumnResult()
      ;

      if ([] === $ids) {
        break;
      }

      foreach ($ids as $id) {
        $extractPath = $this->extractDir.$id;

        if (!is_dir($extractPath)) {
          ++$skipped;
          continue;
        }

        if ($this->deduplicationService->hasExistingMappings($id)) {
          ++$skipped;
          continue;
        }

        if ($dryRun) {
          $io->text("  DRY-RUN {$id}");
          ++$processed;
          continue;
        }

        try {
          $project = $this->entityManager->find(Project::class, $id);
          if (null === $project) {
            ++$skipped;
            continue;
          }

          $this->deduplicationService->deduplicateProject($project, $extractPath);
          ++$processed;
          $io->text("  OK {$id}");
        } catch (\Throwable $e) {
          ++$errors;
          $this->logger->error('Migration failed for project', [
            'project_id' => $id,
            'error' => $e->getMessage(),
          ]);
          $io->warning("  FAIL {$id}: {$e->getMessage()}");
        }

        if ($limit > 0 && $processed >= $limit) {
          break 2;
        }
      }

      $currentOffset += $batchSize;
      $this->entityManager->clear();
    }

    $io->success("Done. Processed: {$processed}, Skipped: {$skipped}, Errors: {$errors}");

    return Command::SUCCESS;
  }
}
