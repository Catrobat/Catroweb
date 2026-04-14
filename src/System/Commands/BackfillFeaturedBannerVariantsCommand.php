<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Project\Special\FeaturedProgram;
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
 * Legacy banners live as `featured_{id}.{jpg|png}` in the featured images dir.
 * This command reads each FeaturedProgram entity with a non-empty image_type,
 * locates the legacy file, and generates the variant set next to it using
 * `featured_{id}` as the basename.
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

    $qb = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from(FeaturedProgram::class, 'f')
      ->where("f.imagetype IS NOT NULL AND f.imagetype != ''")
    ;

    $banners = $qb->getQuery()->toIterable();
    $succeeded = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($banners as $banner) {
      if (!$banner instanceof FeaturedProgram) {
        continue;
      }

      $id = $banner->getId();
      $ext = $banner->getImageType();
      if (null === $id || '' === $ext) {
        continue;
      }

      $basename = 'featured_'.$id;
      $legacy_file = $this->featured_dir.$basename.'.'.$ext;

      if (!is_file($legacy_file)) {
        ++$skipped;
        continue;
      }

      if (ImageVariantLayout::hasVariants($this->featured_dir, $basename)) {
        ++$skipped;
        continue;
      }

      if ($dry_run) {
        ++$succeeded;
        continue;
      }

      try {
        $this->image_variant_generator->generate($legacy_file, $this->featured_dir, $basename);
        ++$succeeded;
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('Banner %s: %s', (string) $id, $e->getMessage()));
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
}
