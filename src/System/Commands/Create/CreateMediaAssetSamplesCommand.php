<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\MediaLibrary\MediaFileType;
use App\DB\EntityRepository\FlavorRepository;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use App\DB\EntityRepository\MediaLibrary\MediaCategoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * CreateMediaAssetSamplesCommand - Creates sample media library assets.
 */
#[AsCommand(name: 'catrobat:create:media-assets-samples', description: 'Create sample media library assets')]
class CreateMediaAssetSamplesCommand extends Command
{
  public function __construct(
    private readonly MediaCategoryRepository $category_repo,
    private readonly MediaAssetRepository $asset_repo,
    private readonly FlavorRepository $flavor_repo,
    private readonly ParameterBagInterface $parameter_bag,
  ) {
    parent::__construct();
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln('Creating media library sample data...');

    /** @var string $sample_path */
    $sample_path = $this->parameter_bag->get('catrobat.media.sample.dir');

    $pocketcode_flavor = $this->flavor_repo->getFlavorByName(Flavor::POCKETCODE);

    if (!$pocketcode_flavor instanceof Flavor) {
      $output->writeln('<error>Pocketcode flavor not found!</error>');

      return Command::FAILURE;
    }

    // Create or get existing categories
    $output->writeln('Setting up categories...');
    $category_figures = $this->category_repo->findOneBy(['name' => 'Figures']);
    if (null === $category_figures) {
      $category_figures = $this->category_repo->createCategory(
        'Figures',
        '',
        1
      );
      $output->writeln('  Created category: Figures');
    } else {
      $output->writeln('  Using existing category: Figures');
    }

    $category_backgrounds = $this->category_repo->findOneBy(['name' => 'Backgrounds']);
    if (null === $category_backgrounds) {
      $category_backgrounds = $this->category_repo->createCategory(
        'Backgrounds',
        '',
        2
      );
      $output->writeln('  Created category: Backgrounds');
    } else {
      $output->writeln('  Using existing category: Backgrounds');
    }

    $category_sounds = $this->category_repo->findOneBy(['name' => 'Sounds']);
    if (null === $category_sounds) {
      $category_sounds = $this->category_repo->createCategory(
        'Sounds',
        '',
        3
      );
      $output->writeln('  Created category: Sounds');
    } else {
      $output->writeln('  Using existing category: Sounds');
    }

    $output->writeln('Creating sample assets...');

    $total_count = 0;
    $success_count = 0;
    $skip_count = 0;

    // Import all figures
    if (is_dir($sample_path.'Figures/')) {
      $output->writeln('  Importing Figures...');
      $result = $this->importDirectory($sample_path.'Figures/', $category_figures, [$pocketcode_flavor], 'Catrobat', $output);
      $total_count += $result['total'];
      $success_count += $result['success'];
      $skip_count += $result['skipped'];
    }

    // Import all backgrounds
    if (is_dir($sample_path.'Backgrounds/')) {
      $output->writeln('  Importing Backgrounds...');
      $result = $this->importDirectory($sample_path.'Backgrounds/', $category_backgrounds, [$pocketcode_flavor], 'Catrobat', $output);
      $total_count += $result['total'];
      $success_count += $result['success'];
      $skip_count += $result['skipped'];
    }

    // Import all sounds
    if (is_dir($sample_path.'Sounds/')) {
      $output->writeln('  Importing Sounds...');
      $result = $this->importDirectory($sample_path.'Sounds/', $category_sounds, [$pocketcode_flavor], 'Catrobat', $output);
      $total_count += $result['total'];
      $success_count += $result['success'];
      $skip_count += $result['skipped'];
    }

    $output->writeln('');
    $output->writeln('<info>Media library sample data created successfully!</info>');
    $output->writeln("<info>Total: {$total_count} | Created: {$success_count} | Skipped: {$skip_count}</info>");

    return Command::SUCCESS;
  }

  /**
   * Import all files from a directory.
   *
   * @param array<Flavor> $flavors
   *
   * @return array{total: int, success: int, skipped: int}
   *
   * @throws \Exception
   */
  private function importDirectory(
    string $directory_path,
    MediaCategory $category,
    array $flavors,
    string $author,
    OutputInterface $output,
  ): array {
    $total = 0;
    $success = 0;
    $skipped = 0;

    $files = scandir($directory_path);
    if (false === $files) {
      return ['total' => 0, 'success' => 0, 'skipped' => 0];
    }

    foreach ($files as $filename) {
      if ('.' === $filename) {
        continue;
      }
      if ('..' === $filename) {
        continue;
      }
      $file_path = $directory_path.$filename;
      if (!is_file($file_path)) {
        continue;
      }

      ++$total;

      // Generate name from filename (remove extension and convert dashes/underscores to spaces)
      $name = pathinfo($filename, PATHINFO_FILENAME);
      $name = str_replace(['-', '_'], ' ', $name);

      try {
        $this->createAsset($name, $file_path, $category, $flavors, $author, $output);
        ++$success;
      } catch (\Exception $e) {
        $output->writeln("<error>    ✗ Failed to import {$filename}: {$e->getMessage()}</error>");
        ++$skipped;
      }
    }

    return ['total' => $total, 'success' => $success, 'skipped' => $skipped];
  }

  /**
   * @param array<Flavor> $flavors
   *
   * @throws \Exception
   */
  private function createAsset(
    string $name,
    string $file_path,
    MediaCategory $category,
    array $flavors,
    string $author,
    OutputInterface $output,
  ): void {
    if (!file_exists($file_path)) {
      throw new \RuntimeException("File not found: {$file_path}");
    }

    $file = new File($file_path);
    $extension = $file->getExtension();

    // Determine file type
    $file_type = MediaFileType::IMAGE;
    $audio_extensions = ['mp3', 'wav', 'ogg', 'm4a', 'mpga'];
    if (in_array(strtolower($extension), $audio_extensions, true)) {
      $file_type = MediaFileType::SOUND;
    }

    $asset = new MediaAsset();
    $asset->setName($name);
    $asset->setFileType($file_type);
    $asset->setExtension($extension);
    $asset->setCategory($category);
    $asset->setFlavors($flavors);
    $asset->setAuthor($author);
    $asset->setActive(true);

    $this->asset_repo->save($asset);
    $this->asset_repo->saveFile($file, $asset->getId(), $extension);

    $output->writeln("    ✓ {$name}");
  }
}
