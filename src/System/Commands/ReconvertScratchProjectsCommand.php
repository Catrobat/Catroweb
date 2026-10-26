<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Project\Project;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use App\Project\Scratch\ScratchManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Converts imported Scratch projects again that an older ScratchToCatrobatConverter::VERSION made, through the
 * normal import (a new version of the same project, same id). Idempotent: converted projects carry the current
 * version and are not picked up again. Projects Scratch no longer serves keep their old copy.
 */
#[AsCommand(
  name: 'catro:scratch:reconvert',
  description: 'Convert imported Scratch projects again that an older converter version made.',
)]
class ReconvertScratchProjectsCommand extends Command
{
  public function __construct(
    private readonly ScratchManager $scratch_manager,
    private readonly EntityManagerInterface $entity_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List the projects without converting them.')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Convert at most N projects (0 = all).', '0')
      ->addOption('pause', null, InputOption::VALUE_REQUIRED, 'Seconds between conversions, to go easy on Scratch.', '5')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $limit = max(0, (int) $input->getOption('limit'));
    $pause = max(0, (int) $input->getOption('pause'));

    $query = $this->entity_manager->createQueryBuilder()
      ->select('p.scratch_id')
      ->from(Project::class, 'p')
      ->where('p.scratch_id IS NOT NULL')
      ->andWhere('p.scratch_converter_version IS NULL OR p.scratch_converter_version < :version')
      ->setParameter('version', ScratchToCatrobatConverter::VERSION)
      ->orderBy('p.scratch_id')
    ;
    if ($limit > 0) {
      $query->setMaxResults($limit);
    }

    /** @var list<int> $scratch_ids */
    $scratch_ids = array_map(intval(...), array_column($query->getQuery()->getScalarResult(), 'scratch_id'));
    $io->note(sprintf('%d project(s) made by a converter older than version %d.', \count($scratch_ids), ScratchToCatrobatConverter::VERSION));
    if ((bool) $input->getOption('dry-run')) {
      $io->listing(array_map(static fn (int $id): string => 'https://scratch.mit.edu/projects/'.$id, $scratch_ids));

      return Command::SUCCESS;
    }

    $converted = 0;
    $kept = 0;
    $failed = 0;
    foreach ($scratch_ids as $i => $scratch_id) {
      if ($i > 0) {
        sleep($pause);
      }

      try {
        $project = $this->convert($scratch_id, $pause);
        if (ScratchToCatrobatConverter::VERSION === $project->getScratchConverterVersion()) {
          ++$converted;
          $io->writeln(sprintf('%d: converted (%s)', $scratch_id, $project->getId() ?? ''));
        } else {
          ++$kept;
          $io->writeln(sprintf('%d: kept, Scratch does not serve it', $scratch_id));
        }
      } catch (ScratchConversionException $exception) {
        ++$failed;
        $io->warning(sprintf('%d: %s', $scratch_id, $exception->getMessage()));
      }

      $this->entity_manager->clear();
    }

    $summary = sprintf('%d converted, %d kept, %d failed.', $converted, $kept, $failed);
    if ($failed > 0) {
      $io->warning($summary);

      return Command::FAILURE;
    }

    $io->success($summary);

    return Command::SUCCESS;
  }

  /** Waits out a running import (the converter takes one at a time) or Scratch's rate limit, three times at most. */
  private function convert(int $scratch_id, int $pause): Project
  {
    for ($attempt = 1;; ++$attempt) {
      try {
        return $this->scratch_manager->importProject($scratch_id, static function (): void {}, null);
      } catch (ScratchConversionException $exception) {
        $retry = \in_array($exception->getCode(), [ScratchConversionException::BUSY, ScratchConversionException::RATE_LIMITED], true);
        if (!$retry || $attempt >= 3) {
          throw $exception;
        }

        sleep(ScratchConversionException::RATE_LIMITED === $exception->getCode() ? 60 : max(1, $pause));
      }
    }
  }
}
