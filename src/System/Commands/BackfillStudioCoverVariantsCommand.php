<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Studio\Studio;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Migrates legacy studio covers to the basename-key format and generates
 * AVIF/WebP variants. Old cover_path values are full relative paths with
 * extension; the new format stores only the basename key.
 *
 * Idempotent: skips studios whose cover_path is already a bare key with
 * variants on disk.
 */
#[AsCommand(
  name: 'catro:backfill:studio-covers',
  description: 'Generate responsive AVIF/WebP variants for existing studio cover images.',
)]
class BackfillStudioCoverVariantsCommand extends Command
{
  private readonly string $pub_dir;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ImageVariantGenerator $image_variant_generator,
    private readonly StudioManager $studio_manager,
    ParameterBagInterface $parameter_bag,
  ) {
    parent::__construct();
    /** @var string $pub_dir */
    $pub_dir = $parameter_bag->get('catrobat.pubdir');
    $this->pub_dir = $pub_dir;
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report work without writing files or updating the DB.')
      ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Studios per flush.', '50')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');
    $batch = max(1, (int) $input->getOption('batch'));
    $studio_dir = $this->studio_manager->getStudioCoverDir();

    $studios = $this->entity_manager->createQueryBuilder()
      ->select('s')
      ->from(Studio::class, 's')
      ->where('s.cover_path IS NOT NULL')
      ->andWhere("s.cover_path != ''")
      ->getQuery()
      ->toIterable()
    ;

    $succeeded = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($studios as $studio) {
      if (!$studio instanceof Studio) {
        continue;
      }

      $cover_path = $studio->getCoverAssetPath();
      if (null === $cover_path || '' === $cover_path) {
        continue;
      }

      // Already a bare key (no slash, no extension) with variants on disk → skip.
      if (!str_contains($cover_path, '/') && !str_contains($cover_path, '.')) {
        if (ImageVariantLayout::hasVariants($studio_dir, $cover_path)) {
          ++$skipped;
          continue;
        }
      }

      try {
        if ($dry_run) {
          ++$succeeded;
          continue;
        }

        $this->migrateOne($studio, $cover_path, $studio_dir);
        ++$succeeded;

        if (0 === $succeeded % $batch) {
          $this->entity_manager->flush();
          $this->entity_manager->clear();
        }
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('Studio %s: %s', $studio->getId() ?? '?', $e->getMessage()));
      }
    }

    if (!$dry_run) {
      $this->entity_manager->flush();
      $this->entity_manager->clear();
    }

    $io->note(sprintf('Skipped (already migrated): %d', $skipped));

    if ($failed > 0) {
      $io->warning(sprintf('Done with errors. %d succeeded, %d failed.', $succeeded, $failed));

      return Command::FAILURE;
    }

    $io->success(sprintf('Done. %d studio(s) %s.', $succeeded, $dry_run ? 'would be migrated' : 'migrated'));

    return Command::SUCCESS;
  }

  private function migrateOne(Studio $studio, string $cover_path, string $studio_dir): void
  {
    $legacy_file = $this->findLegacyFile($cover_path, $studio_dir);
    if (null === $legacy_file) {
      throw new \RuntimeException(sprintf('Legacy cover file not found for cover_path=%s', $cover_path));
    }

    $basename = pathinfo(basename($cover_path), PATHINFO_FILENAME);
    if ('' === $basename) {
      throw new \RuntimeException(sprintf('Could not derive basename from cover_path=%s', $cover_path));
    }

    $this->image_variant_generator->generate($legacy_file, $studio_dir, $basename);
    $studio->setCoverAssetPath($basename);

    // Safe to remove: generate() succeeded, so all variant files are on disk.
    @unlink($legacy_file);
  }

  private function findLegacyFile(string $cover_path, string $studio_dir): ?string
  {
    if (str_contains($cover_path, '/')) {
      $candidate = rtrim($this->pub_dir, '/').'/'.$cover_path;
      if (is_file($candidate)) {
        return $candidate;
      }
    }

    $candidate = $studio_dir.basename($cover_path);

    return is_file($candidate) ? $candidate : null;
  }
}
