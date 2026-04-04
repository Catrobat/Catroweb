<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\Storage\ContentAddressableStore;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProjectZipReconstructor
{
  public function __construct(
    private readonly ProjectAssetMappingRepository $mappingRepository,
    private readonly ContentAddressableStore $store,
    private readonly LoggerInterface $logger,
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extractDir,
    #[Autowire('%catrobat.file.storage.dir%')]
    private readonly string $zipDir,
  ) {
  }

  /**
   * Reconstruct the .catrobat ZIP for a project from its asset mappings
   * and extracted code.xml. Caches the result in the programs/ directory.
   *
   * Returns the absolute path to the reconstructed ZIP, or null on failure.
   */
  public function reconstruct(string $projectId): ?string
  {
    $zipPath = $this->zipDir.$projectId.'.catrobat';

    if (file_exists($zipPath)) {
      return $zipPath;
    }

    $extractPath = $this->extractDir.$projectId;
    if (!is_dir($extractPath)) {
      $this->logger->error('Cannot reconstruct ZIP: extracted directory missing', [
        'project_id' => $projectId,
      ]);

      return null;
    }

    $mappings = $this->mappingRepository->findByProjectId($projectId);
    if ([] === $mappings) {
      return null;
    }

    $zip = new \ZipArchive();
    $tempPath = $zipPath.'.tmp.'.bin2hex(random_bytes(4));

    if (true !== $zip->open($tempPath, \ZipArchive::CREATE)) {
      $this->logger->error('Cannot create ZIP archive', ['path' => $tempPath]);

      return null;
    }

    // Add code.xml from extracted directory
    $codeXmlPath = $extractPath.'/code.xml';
    if (file_exists($codeXmlPath)) {
      $zip->addFile($codeXmlPath, 'code.xml');
    }

    // Add screenshots if present
    foreach (['manual_screenshot.png', 'automatic_screenshot.png', 'screenshot.png'] as $screenshot) {
      $screenshotPath = $extractPath.'/'.$screenshot;
      if (file_exists($screenshotPath)) {
        $zip->addFile($screenshotPath, $screenshot);
      }
    }

    // Add all mapped assets from content-addressable store
    foreach ($mappings as $mapping) {
      $asset = $mapping->getAsset();
      $assetPath = $this->store->getAbsolutePathFromRelative($asset->getStoragePath());

      if (!file_exists($assetPath)) {
        $this->logger->warning('Asset file missing from store', [
          'hash' => $asset->getHash(),
          'path' => $asset->getStoragePath(),
          'project_id' => $projectId,
        ]);
        // Fall back to extracted file if available
        $fallback = $extractPath.'/'.$mapping->getPathInZip();
        if (file_exists($fallback)) {
          $zip->addFile($fallback, $mapping->getPathInZip());
        }

        continue;
      }

      $zip->addFile($assetPath, $mapping->getPathInZip());
    }

    // Add any other files from extract dir not covered by mappings
    $this->addRemainingFiles($zip, $extractPath, $mappings);

    $zip->close();

    // Atomic move to final path
    rename($tempPath, $zipPath);

    return $zipPath;
  }

  public function invalidateCache(string $projectId): void
  {
    $zipPath = $this->zipDir.$projectId.'.catrobat';
    if (file_exists($zipPath)) {
      unlink($zipPath);
    }
  }

  /**
   * @param list<\App\DB\Entity\Project\ProjectAssetMapping> $mappings
   */
  private function addRemainingFiles(\ZipArchive $zip, string $extractPath, array $mappings): void
  {
    $coveredPaths = ['code.xml', 'manual_screenshot.png', 'automatic_screenshot.png', 'screenshot.png'];
    foreach ($mappings as $mapping) {
      $coveredPaths[] = $mapping->getPathInZip();
    }

    $coveredSet = array_flip($coveredPaths);

    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($extractPath, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::LEAVES_ONLY,
    );

    foreach ($iterator as $file) {
      if (!$file->isFile()) {
        continue;
      }

      $relativePath = substr($file->getPathname(), strlen($extractPath) + 1);
      if (!isset($coveredSet[$relativePath])) {
        $zip->addFile($file->getPathname(), $relativePath);
      }
    }
  }
}
