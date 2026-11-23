<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Project\Project;
use App\Project\CodeStatistics\CodeStatisticsParser;
use App\Project\CodeStatistics\ProjectBrickUsageWriter;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Fills project_brick_usage for projects uploaded before it existed. A project with code statistics gets the brick
 * counts of its newest row; one without gets statistics parsed from the code.xml in its stored .catrobat file
 * (read from the zip, nothing is extracted to disk). Projects that already have usage rows are skipped, so the
 * command is safe to rerun and to run alongside production traffic. It walks the projects in id order and prints
 * the last id of every batch: pass it as --after to resume.
 */
#[AsCommand(
  name: 'catro:backfill:brick-usage',
  description: 'Fill project_brick_usage, parsing code statistics for projects that have none.',
)]
class BackfillBrickUsageCommand extends Command
{
  public function __construct(
    private readonly Connection $connection,
    private readonly EntityManagerInterface $entity_manager,
    private readonly CodeStatisticsParser $parser,
    private readonly ProjectBrickUsageWriter $usage_writer,
    #[Autowire('%catrobat.file.storage.dir%')]
    private readonly string $storage_dir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Projects per batch.', '500')
      ->addOption('after', null, InputOption::VALUE_REQUIRED, 'Resume after this project id.', '')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $batch_size = max(1, (int) $input->getOption('batch-size'));
    /** @var string $after */
    $after = $input->getOption('after');
    $totals = ['skipped' => 0, 'copied' => 0, 'parsed' => 0, 'no_file' => 0];

    while (true) {
      /** @var list<string> $ids */
      $ids = $this->connection->fetchFirstColumn(
        'SELECT id FROM program WHERE id > ? ORDER BY id LIMIT '.$batch_size,
        [$after],
      );
      if ([] === $ids) {
        break;
      }

      foreach ($this->processBatch($ids) as $key => $count) {
        $totals[$key] += $count;
      }
      $after = $ids[array_key_last($ids)];
      $io->writeln(sprintf('Up to %s: %d skipped, %d copied, %d parsed, %d without file', $after, ...array_values($totals)), OutputInterface::VERBOSITY_VERBOSE);
    }

    $io->success(sprintf(
      '%d project(s) already had usage, %d copied from statistics, %d parsed, %d without a readable code.xml.',
      ...array_values($totals),
    ));

    return Command::SUCCESS;
  }

  /**
   * @param list<string> $ids
   *
   * @return array{skipped: int, copied: int, parsed: int, no_file: int}
   */
  private function processBatch(array $ids): array
  {
    $counts = ['skipped' => 0, 'copied' => 0, 'parsed' => 0, 'no_file' => 0];

    $with_usage = array_flip($this->connection->fetchFirstColumn(
      'SELECT DISTINCT project_id FROM project_brick_usage WHERE project_id IN (?)',
      [$ids], [ArrayParameterType::STRING],
    ));

    // Ascending order: the newest statistics row of a project overwrites the older ones
    $latest_brick_counts = [];
    $rows = $this->connection->fetchAllAssociative(
      'SELECT program_id, brick_counts FROM project_code_statistics WHERE program_id IN (?) ORDER BY created_at, id',
      [$ids], [ArrayParameterType::STRING],
    );
    foreach ($rows as $row) {
      $latest_brick_counts[(string) $row['program_id']] = (string) $row['brick_counts'];
    }

    foreach ($ids as $id) {
      if (isset($with_usage[$id])) {
        ++$counts['skipped'];
      } elseif (isset($latest_brick_counts[$id])) {
        /** @var array<string, int> $brick_counts */
        $brick_counts = json_decode($latest_brick_counts[$id], true) ?: [];
        $this->usage_writer->replace($id, $brick_counts);
        ++$counts['copied'];
      } elseif (null !== ($xml = $this->readCodeXml($id)) && null !== ($project = $this->entity_manager->getReference(Project::class, $id))) {
        // Persisting the statistics writes the usage rows (ProjectBrickUsageWriter)
        $stats = $this->parser->parseXml($xml);
        $stats->setProject($project);
        $this->entity_manager->persist($stats);
        ++$counts['parsed'];
      } else {
        ++$counts['no_file'];
      }
    }

    // A console command, not a message handler: clearing keeps memory flat across hundreds of thousands of projects
    $this->entity_manager->flush();
    $this->entity_manager->clear();

    return $counts;
  }

  private function readCodeXml(string $id): ?string
  {
    $path = $this->storage_dir.$id.'.catrobat';
    $zip = new \ZipArchive();
    if (!is_file($path) || true !== $zip->open($path, \ZipArchive::RDONLY)) {
      return null;
    }

    // Some apps wrap the project in a subdirectory
    $index = $zip->locateName('code.xml', \ZipArchive::FL_NODIR);
    $xml = false === $index ? false : $zip->getFromIndex($index);
    $zip->close();

    return false === $xml ? null : $xml;
  }
}
