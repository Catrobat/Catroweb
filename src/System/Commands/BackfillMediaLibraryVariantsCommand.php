<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Walks thumbnail files in the media library thumbs directory and generates
 * the AVIF/WebP variant set next to each. Filesystem-only — no DB updates.
 *
 * Idempotent, safe to run alongside production traffic.
 */
#[AsCommand(
  name: 'catro:backfill:media-library-thumbnails',
  description: 'Generate responsive AVIF/WebP variants for existing media library thumbnails.',
)]
class BackfillMediaLibraryVariantsCommand extends Command
{
  public function __construct(
    private readonly ImageVariantGenerator $image_variant_generator,
    private readonly MediaAssetRepository $asset_repository,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Count files without generating variants.')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Process at most N thumbnails (0 = unlimited).', '0')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');
    $limit = max(0, (int) $input->getOption('limit'));
    $thumb_dir = $this->asset_repository->getThumbnailDir();

    $finder = new Finder();
    $finder->files()->in($thumb_dir)->depth('== 0')
      ->name('/\.(png|jpg|jpeg|gif|bmp|webp|svg)$/i')
      ->notName('/-(thumb|card|detail)@[12]x\.(avif|webp)$/')
    ;

    $total = $finder->count();
    $io->note(sprintf('Found %d thumbnail file(s) in %s', $total, $thumb_dir));

    $succeeded = 0;
    $skipped = 0;
    $failed = 0;
    $progress = $io->createProgressBar($limit > 0 ? min($limit, $total) : $total);
    $progress->setFormat('verbose');

    foreach ($finder as $file) {
      if ($limit > 0 && ($succeeded + $skipped + $failed) >= $limit) {
        break;
      }

      $progress->advance();

      // The basename is the asset UUID (without extension)
      $basename = $file->getFilenameWithoutExtension();

      if (ImageVariantLayout::hasVariants($thumb_dir, $basename)) {
        ++$skipped;
        continue;
      }

      if ($dry_run) {
        ++$succeeded;
        continue;
      }

      try {
        $this->image_variant_generator->generate($file->getRealPath(), $thumb_dir, $basename);
        ++$succeeded;
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('%s: %s', $file->getFilename(), $e->getMessage()));
      }
    }

    $progress->finish();
    $io->newLine(2);
    $io->note(sprintf('Skipped (already have variants): %d', $skipped));

    if ($failed > 0) {
      $io->warning(sprintf('Done with errors. %d succeeded, %d failed.', $succeeded, $failed));

      return Command::FAILURE;
    }

    $io->success(sprintf('Done. %d thumbnail(s) %s.', $succeeded, $dry_run ? 'would be processed' : 'processed'));

    return Command::SUCCESS;
  }
}
