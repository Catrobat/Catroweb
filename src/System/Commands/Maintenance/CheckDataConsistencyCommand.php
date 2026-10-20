<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use App\Storage\ScreenshotRepository;
use Doctrine\DBAL\ArrayParameterType;
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

  /** --hide refuses to act when more than this share of the scanned projects would be hidden. */
  private const float HIDE_MAX_RATIO = 0.05;

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
      ->addOption('force', null, InputOption::VALUE_NONE, sprintf('Let --hide act even when more than %d%% of the scanned projects would be hidden.', (int) (self::HIDE_MAX_RATIO * 100)))
      ->addOption('project', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only check these project ids (repeatable).')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $hide = (bool) $input->getOption('hide');
    $regenerate = (bool) $input->getOption('regenerate-thumbnails');
    /** @var list<string> $only_ids */
    $only_ids = $input->getOption('project');
    $screenshot_dir = $this->screenshot_repository->getScreenshotDir();

    /** @var array<string, list<string>> $found */
    $found = ['missing_project_file' => [], 'missing_screenshot' => [], 'missing_thumbnail' => [], 'missing_variants' => []];
    $regenerated = 0;
    $failed = 0;
    $scanned = 0;

    $conn = $this->entity_manager->getConnection();
    $table = $this->entity_manager->getClassMetadata(Project::class)->getTableName();
    $last_id = '';
    $id_filter = [] !== $only_ids ? ' AND id IN (:only)' : '';
    while ([] !== ($ids = $conn->fetchFirstColumn(
      sprintf('SELECT id FROM %s WHERE id > :last%s ORDER BY id LIMIT %d', $table, $id_filter, self::BATCH_SIZE),
      [] !== $only_ids ? ['last' => $last_id, 'only' => $only_ids] : ['last' => $last_id],
      ['only' => ArrayParameterType::STRING],
    ))) {
      foreach ($ids as $id) {
        $id = (string) $id;
        $last_id = $id;
        ++$scanned;

        // The zip and the extracted code.xml can each be rebuilt from the other.
        if (!$this->project_file_repository->checkIfProjectZipFileExists($id) && !is_file($this->extract_dir.$id.'/code.xml')) {
          $found['missing_project_file'][] = $id;
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
    }

    $hide_failed = false;
    $missing_count = count($found['missing_project_file']);
    if ($hide && $missing_count > 0) {
      if ($missing_count > $scanned * self::HIDE_MAX_RATIO && !$input->getOption('force')) {
        // Zips and extracted dirs are caches that cron cleans up; a mass hit
        // usually means a storage problem, not a mass of broken projects.
        $io->error(sprintf('Refusing to hide %d of %d projects (> %d%%). Check storage first; --force overrides.', $missing_count, $scanned, (int) (self::HIDE_MAX_RATIO * 100)));
        $hide_failed = true;
      } else {
        foreach (array_chunk($found['missing_project_file'], self::BATCH_SIZE) as $chunk) {
          foreach ($this->hideProjects($chunk) as $hidden_id) {
            // Always printed: this is the record of what --hide changed, for an undo.
            $output->writeln('hidden: '.$hidden_id, OutputInterface::VERBOSITY_QUIET);
          }
        }
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

    if ($regenerate) {
      $io->note(sprintf('Regenerated images for %d project(s), %d failed.', $regenerated, $failed));
    }

    if (!$hide && !$regenerate) {
      $io->note('Dry run. Use --hide and/or --regenerate-thumbnails to fix.');
    }

    return $failed > 0 || $hide_failed ? Command::FAILURE : Command::SUCCESS;
  }

  /**
   * Goes through the ORM so search-index listeners see the change.
   *
   * @param list<string> $ids
   *
   * @return list<string> ids that were visible before and are hidden now
   */
  private function hideProjects(array $ids): array
  {
    $hidden = [];
    foreach ($this->entity_manager->getRepository(Project::class)->findBy(['id' => $ids]) as $project) {
      if ($project->getVisible()) {
        $hidden[] = (string) $project->getId();
      }

      $project->setVisible(false);
      $project->setHasMissingFiles(true);
    }

    $this->entity_manager->flush();
    $this->entity_manager->clear();

    return $hidden;
  }
}
