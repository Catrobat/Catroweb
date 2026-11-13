<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The .catrobat zip is only a cache (emptied by catrobat:clean:compressed, rebuilt by ProjectZipReconstructor from
 * the extracted directory), so a project is broken only when both the zip and the extracted code.xml are gone.
 */
#[AsCommand(name: 'catrobat:workflow:detect_broken_projects', description: 'Detect projects whose files are lost (no .catrobat zip and no extracted code.xml) and flag them.')]
class DetectBrokenProjectsCommand extends Command
{
  private const int BATCH_SIZE = 100;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ProjectFileRepository $file_repository,
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extract_dir,
    #[Autowire(service: 'fos_elastica.object_persister.app_program')]
    private readonly ObjectPersisterInterface $program_persister,
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
        ->select('p.id', 'p.has_missing_files')
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
        $file_exists = $this->file_repository->checkIfProjectZipFileExists($id)
          || is_file($this->extract_dir.$id.'/code.xml');

        if (!$file_exists && !$project['has_missing_files']) {
          $broken_ids[] = $id;
        } elseif ($file_exists && $project['has_missing_files']) {
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

      $this->reindex([...$broken_ids, ...$fixed_ids], $io);

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

  /**
   * The bulk DQL updates bypass the FOS Elastica listeners, so search would keep the old flag.
   *
   * @param string[] $ids
   */
  private function reindex(array $ids, SymfonyStyle $io): void
  {
    if ([] === $ids) {
      return;
    }

    try {
      $this->program_persister->replaceMany($this->entity_manager->getRepository(Project::class)->findBy(['id' => $ids]));
    } catch (\Throwable $e) {
      $io->warning('Search index not updated for '.count($ids).' project(s): '.$e->getMessage());
    }
  }
}
