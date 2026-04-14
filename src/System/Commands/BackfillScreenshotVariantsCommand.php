<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use App\Storage\ScreenshotRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Walks `screen_{uuid}.png` files in the screenshots directory and generates
 * the AVIF/WebP variant set next to each. Filesystem-only — no DB updates.
 *
 * Idempotent, safe to run alongside production traffic.
 */
#[AsCommand(
  name: 'catro:backfill:screenshots',
  description: 'Generate responsive AVIF/WebP variants for existing project screenshots.',
)]
class BackfillScreenshotVariantsCommand extends Command
{
  public function __construct(
    private readonly ImageVariantGenerator $image_variant_generator,
    private readonly ScreenshotRepository $screenshot_repository,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Count files without generating variants.')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Process at most N screenshots (0 = unlimited).', '0')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');
    $limit = max(0, (int) $input->getOption('limit'));
    $screenshot_dir = $this->screenshot_repository->getScreenshotDir();

    $finder = new Finder();
    $finder->files()->in($screenshot_dir)->name('screen_*.png')->depth('== 0');

    $total = $finder->count();
    $io->note(sprintf('Found %d legacy screenshot PNGs in %s', $total, $screenshot_dir));

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

      if (!preg_match('/^screen_(.+)\.png$/', $file->getFilename(), $matches)) {
        ++$skipped;
        continue;
      }

      $basename = $this->screenshot_repository->getScreenshotVariantBasename($matches[1]);

      if (ImageVariantLayout::hasVariants($screenshot_dir, $basename)) {
        ++$skipped;
        continue;
      }

      if ($dry_run) {
        ++$succeeded;
        continue;
      }

      try {
        $this->image_variant_generator->generate($file->getRealPath(), $screenshot_dir, $basename);
        ++$succeeded;
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('screen_%s.png: %s', $matches[1], $e->getMessage()));
      }
    }

    $progress->finish();
    $io->newLine(2);
    $io->note(sprintf('Skipped (already have variants): %d', $skipped));

    if ($failed > 0) {
      $io->warning(sprintf('Done with errors. %d succeeded, %d failed.', $succeeded, $failed));

      return Command::FAILURE;
    }

    $io->success(sprintf('Done. %d screenshot(s) %s.', $succeeded, $dry_run ? 'would be processed' : 'processed'));

    return Command::SUCCESS;
  }
}
