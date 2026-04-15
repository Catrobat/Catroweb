<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'catrobat:workflow:detect_broken_projects', description: 'Detect projects with missing .catrobat files and flag them.')]
class DetectBrokenProjectsCommand extends Command
{
  private const int BATCH_SIZE = 100;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ProjectFileRepository $file_repository,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $io->title('Detecting broken projects');

    $total_checked = 0;
    $newly_broken = 0;
    $newly_fixed = 0;

    $offset = 0;

    while (true) {
      $projects = $this->entity_manager->createQueryBuilder()
        ->select('p.id')
        ->from(Project::class, 'p')
        ->orderBy('p.id', 'ASC')
        ->setFirstResult($offset)
        ->setMaxResults(self::BATCH_SIZE)
        ->getQuery()
        ->getArrayResult()
      ;

      if ([] === $projects) {
        break;
      }

      $broken_ids = [];
      $fixed_ids = [];

      foreach ($projects as $project) {
        $id = $project['id'];
        $file_exists = $this->file_repository->checkIfProjectZipFileExists($id);

        if (!$file_exists) {
          $broken_ids[] = $id;
        } else {
          $fixed_ids[] = $id;
        }

        ++$total_checked;
      }

      // Batch update broken projects that were not already flagged
      if ([] !== $broken_ids) {
        $updated = (int) $this->entity_manager->createQueryBuilder()
          ->update(Project::class, 'p')
          ->set('p.has_missing_files', ':true')
          ->where('p.id IN (:ids)')
          ->andWhere('p.has_missing_files = :false')
          ->setParameter('true', true)
          ->setParameter('false', false)
          ->setParameter('ids', $broken_ids)
          ->getQuery()
          ->execute()
        ;
        $newly_broken += $updated;
      }

      // Batch update projects that were flagged but are now fixed
      if ([] !== $fixed_ids) {
        $updated = (int) $this->entity_manager->createQueryBuilder()
          ->update(Project::class, 'p')
          ->set('p.has_missing_files', ':false')
          ->where('p.id IN (:ids)')
          ->andWhere('p.has_missing_files = :true')
          ->setParameter('true', true)
          ->setParameter('false', false)
          ->setParameter('ids', $fixed_ids)
          ->getQuery()
          ->execute()
        ;
        $newly_fixed += $updated;
      }

      $offset += self::BATCH_SIZE;

      // Clear entity manager to free memory
      $this->entity_manager->clear();
    }

    $io->success(sprintf(
      'Done. Checked %d projects: %d newly flagged as broken, %d previously broken now fixed.',
      $total_checked,
      $newly_broken,
      $newly_fixed,
    ));

    return Command::SUCCESS;
  }
}
