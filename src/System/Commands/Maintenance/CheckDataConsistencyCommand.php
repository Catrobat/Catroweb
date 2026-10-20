<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reports DB rows whose files are gone: projects without a project file,
 * projects without screenshot / thumbnail / image variants, and content
 * reports pointing at content that no longer exists.
 *
 * Read-only unless --hide or --regenerate-thumbnails is given. Never deletes.
 */
#[AsCommand(
  name: 'catrobat:check:data-consistency',
  description: 'Report projects with missing files and reports pointing at missing content.',
)]
class CheckDataConsistencyCommand extends Command
{
  private const int BATCH_SIZE = 500;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ProjectFileRepository $project_file_repository,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly ImageVariantGenerator $image_variant_generator,
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extract_dir,
    #[Autowire('%catrobat.thumbnail.dir%')]
    private readonly string $thumbnail_dir,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('hide', null, InputOption::VALUE_NONE, 'Set projects without a project file invisible and flag them as broken (never deletes).')
      ->addOption('regenerate-thumbnails', null, InputOption::VALUE_NONE, 'Regenerate missing thumbnails and image variants where the screenshot exists.')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $hide = (bool) $input->getOption('hide');
    $regenerate = (bool) $input->getOption('regenerate-thumbnails');
    $screenshot_dir = $this->screenshot_repository->getScreenshotDir();

    /** @var array<string, list<string>> $found */
    $found = ['missing_project_file' => [], 'missing_screenshot' => [], 'missing_thumbnail' => [], 'missing_variants' => []];
    $regenerated = 0;
    $failed = 0;
    $hidden = 0;

    $conn = $this->entity_manager->getConnection();
    $table = $this->entity_manager->getClassMetadata(Project::class)->getTableName();
    $last_id = '';
    while ([] !== ($ids = $conn->fetchFirstColumn(
      sprintf('SELECT id FROM %s WHERE id > ? ORDER BY id LIMIT %d', $table, self::BATCH_SIZE),
      [$last_id],
    ))) {
      $missing_file_ids = [];
      foreach ($ids as $id) {
        $id = (string) $id;
        $last_id = $id;

        // The zip and the extracted directory can each be rebuilt from the other.
        if (!$this->project_file_repository->checkIfProjectZipFileExists($id) && !is_dir($this->extract_dir.$id)) {
          $missing_file_ids[] = $id;
        }

        $screenshot = $screenshot_dir.'screen_'.$id.'.png';
        $thumbnail = $this->thumbnail_dir.'screen_'.$id.'.png';
        $basename = $this->screenshot_repository->getScreenshotVariantBasename($id);
        if (!is_file($screenshot)) {
          $found['missing_screenshot'][] = $id;
          continue;
        }

        $has_thumbnail = is_file($thumbnail);
        $has_variants = ImageVariantLayout::hasVariants($screenshot_dir, $basename);
        if (!$has_thumbnail) {
          $found['missing_thumbnail'][] = $id;
        }

        if (!$has_variants) {
          $found['missing_variants'][] = $id;
        }

        if (!$regenerate || ($has_thumbnail && $has_variants)) {
          continue;
        }

        try {
          if (!$has_thumbnail) {
            $this->screenshot_repository->saveThumbnail($screenshot, $id);
          }

          if (!$has_variants) {
            $this->image_variant_generator->generate($screenshot, $screenshot_dir, $basename);
          }

          ++$regenerated;
        } catch (\Throwable $e) {
          ++$failed;
          $io->warning(sprintf('Regenerating images for %s failed: %s', $id, $e->getMessage()));
        }
      }

      $found['missing_project_file'] = [...$found['missing_project_file'], ...$missing_file_ids];
      if ($hide && [] !== $missing_file_ids) {
        $hidden += $this->hideProjects($missing_file_ids);
      }
    }

    /** @var list<array{id: string, content_type: string, content_id: string}> $orphaned_reports */
    $orphaned_reports = $conn->fetchAllAssociative(
      "SELECT r.id, r.content_type, r.content_id
       FROM content_report r
       LEFT JOIN program p ON r.content_type = 'project' AND p.id = r.content_id
       LEFT JOIN user_comment c ON r.content_type = 'comment' AND c.id = r.content_id
       LEFT JOIN fos_user u ON r.content_type = 'user' AND u.id = r.content_id
       LEFT JOIN studio s ON r.content_type = 'studio' AND s.id = r.content_id
       WHERE p.id IS NULL AND c.id IS NULL AND u.id IS NULL AND s.id IS NULL
       ORDER BY r.id"
    );

    $io->table(['Check', 'Count'], [
      ['Projects without project file', count($found['missing_project_file'])],
      ['Projects without screenshot', count($found['missing_screenshot'])],
      ['Projects without thumbnail', count($found['missing_thumbnail'])],
      ['Projects without image variants', count($found['missing_variants'])],
      ['Reports pointing at missing content', count($orphaned_reports)],
    ]);

    if ($output->isVerbose()) {
      foreach ($found as $check => $ids) {
        foreach ($ids as $id) {
          $output->writeln(sprintf('%s: %s', $check, $id));
        }
      }

      foreach ($orphaned_reports as $report) {
        $output->writeln(sprintf('report_missing_target: %s (%s %s)', $report['id'], $report['content_type'], $report['content_id']));
      }
    }

    if ($hide) {
      $io->note(sprintf('Hid %d project(s) without project file.', $hidden));
    }

    if ($regenerate) {
      $io->note(sprintf('Regenerated images for %d project(s), %d failed.', $regenerated, $failed));
    }

    if (!$hide && !$regenerate) {
      $io->note('Dry run. Use --hide and/or --regenerate-thumbnails to fix.');
    }

    return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
  }

  /**
   * Goes through the ORM so search-index listeners see the change.
   *
   * @param list<string> $ids
   */
  private function hideProjects(array $ids): int
  {
    $count = 0;
    foreach ($this->entity_manager->getRepository(Project::class)->findBy(['id' => $ids]) as $project) {
      if ($project->getVisible()) {
        ++$count;
      }

      $project->setVisible(false);
      $project->setHasMissingFiles(true);
    }

    $this->entity_manager->flush();
    $this->entity_manager->clear();

    return $count;
  }
}
