<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\FeaturedBanner;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Generates AVIF/WebP variants for existing featured-banner images.
 *
 * Handles two cases:
 * 1. Banners with uploaded images (image_type set) — reads the legacy file.
 * 2. Video banners — downloads the YouTube thumbnail as source.
 *
 * Idempotent: skips any banner whose WebP thumb variant already exists.
 */
#[AsCommand(
  name: 'catro:backfill:featured-banners',
  description: 'Generate responsive AVIF/WebP variants for existing featured banner images.',
)]
class BackfillFeaturedBannerVariantsCommand extends Command
{
  private readonly string $featured_dir;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ImageVariantGenerator $image_variant_generator,
    ParameterBagInterface $parameter_bag,
  ) {
    parent::__construct();
    /** @var string $dir */
    $dir = $parameter_bag->get('catrobat.featuredimage.dir');
    $this->featured_dir = $dir;
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Count banners without generating variants.')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');

    $banners = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from(FeaturedBanner::class, 'f')
      ->getQuery()
      ->toIterable()
    ;

    $succeeded = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($banners as $banner) {
      if (!$banner instanceof FeaturedBanner) {
        continue;
      }

      $id = $banner->getId();
      if (null === $id) {
        continue;
      }

      $basename = 'featured_'.$id;

      if (ImageVariantLayout::hasVariants($this->featured_dir, $basename)) {
        ++$skipped;
        continue;
      }

      $source_file = $this->resolveSourceFile($banner, $basename);
      if (null === $source_file) {
        ++$skipped;
        continue;
      }

      if ($dry_run) {
        ++$succeeded;
        continue;
      }

      try {
        $this->image_variant_generator->generate($source_file, $this->featured_dir, $basename);
        ++$succeeded;
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('Banner %s: %s', $id, $e->getMessage()));
      } finally {
        // Clean up downloaded temp files
        if (str_starts_with($source_file, rtrim(sys_get_temp_dir(), '/').'/')) {
          @unlink($source_file);
        }
      }
    }

    $this->entity_manager->clear();

    $io->note(sprintf('Skipped (missing file or already migrated): %d', $skipped));

    if ($failed > 0) {
      $io->warning(sprintf('Done with errors. %d succeeded, %d failed.', $succeeded, $failed));

      return Command::FAILURE;
    }

    $io->success(sprintf('Done. %d banner(s) %s.', $succeeded, $dry_run ? 'would be processed' : 'processed'));

    return Command::SUCCESS;
  }

  private function resolveSourceFile(FeaturedBanner $banner, string $basename): ?string
  {
    $ext = $banner->getImageType();
    if ('' !== $ext) {
      $file = $this->featured_dir.$basename.'.'.$ext;

      return is_file($file) ? $file : null;
    }

    if ('video' === $banner->getType()) {
      return $this->downloadYouTubeThumbnail($banner);
    }

    return null;
  }

  private function downloadYouTubeThumbnail(FeaturedBanner $banner): ?string
  {
    $video_url = $banner->getVideoUrl();
    if (null === $video_url || 1 !== preg_match('#/embed/([a-zA-Z0-9_-]+)#', $video_url, $m)) {
      return null;
    }

    $video_id = $m[1];

    $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);

    foreach (['maxresdefault', 'hqdefault'] as $quality) {
      $url = 'https://img.youtube.com/vi/'.$video_id.'/'.$quality.'.jpg';
      $data = @file_get_contents($url, false, $ctx);
      if (false === $data || '' === $data) {
        continue;
      }

      // Verify we got an actual image (YouTube returns a tiny placeholder for missing resolutions)
      if (strlen($data) < 1000) {
        continue;
      }

      $tmp = tempnam(sys_get_temp_dir(), 'yt_thumb_');
      if (false !== $tmp) {
        file_put_contents($tmp, $data);

        return $tmp;
      }
    }

    return null;
  }
}
