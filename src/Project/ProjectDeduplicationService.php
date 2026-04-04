<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Storage\ContentAddressableStore;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ProjectDeduplicationService
{
  /** Files to hash and deduplicate (binary assets, not XML metadata). */
  private const array ASSET_DIRECTORIES = ['images', 'sounds'];

  public function __construct(
    private readonly ContentAddressableStore $store,
    private readonly ProjectAssetRepository $assetRepository,
    private readonly ProjectAssetMappingRepository $mappingRepository,
    private readonly EntityManagerInterface $entityManager,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Process a newly extracted project: hash all assets, store unique ones,
   * create mappings, update reference counts.
   */
  public function deduplicateProject(Program $project, string $extractDir): void
  {
    $assetFiles = $this->collectAssetFiles($extractDir);

    if ([] === $assetFiles) {
      return;
    }

    foreach ($assetFiles as $assetFile) {
      $absolutePath = $assetFile['absolutePath'];
      $pathInZip = $assetFile['pathInZip'];
      $filename = basename($pathInZip);

      $hash = $this->store->hashFile($absolutePath);
      $size = (int) filesize($absolutePath);
      $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';

      $asset = $this->assetRepository->findByHash($hash);
      if (null === $asset) {
        $storagePath = $this->store->store($absolutePath, $hash);
        $asset = new ProjectAsset($hash, $size, $mimeType, $storagePath);
        try {
          $this->entityManager->persist($asset);
          $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
          $this->entityManager->clear();
          $asset = $this->assetRepository->findByHash($hash);
          if (null === $asset) {
            $this->logger->error('Failed to find asset after UniqueConstraintViolation', ['hash' => $hash]);
            continue;
          }
        }
      }

      $asset->incrementReferenceCount();

      $mapping = new ProjectAssetMapping($project, $asset, $filename, $pathInZip);
      $this->entityManager->persist($mapping);
    }

    $this->entityManager->flush();

    $this->logger->info('Deduplicated project assets', [
      'project_id' => $project->getId(),
      'total_files' => count($assetFiles),
    ]);
  }

  /**
   * Remove all asset mappings for a project and decrement reference counts.
   */
  public function removeProjectMappings(string $projectId): void
  {
    $mappings = $this->mappingRepository->findByProjectId($projectId);

    foreach ($mappings as $mapping) {
      $asset = $mapping->getAsset();
      $asset->decrementReferenceCount();
      $this->entityManager->remove($mapping);
    }

    $this->entityManager->flush();
  }

  /**
   * Garbage-collect orphaned assets (referenceCount <= 0).
   * Intended to be called from a cron/command, NOT inline during upload.
   */
  public function garbageCollect(int $limit = 100): int
  {
    $orphans = $this->assetRepository->findOrphanedAssets($limit);
    $deleted = 0;

    foreach ($orphans as $asset) {
      $this->store->delete($asset->getHash());
      $this->entityManager->remove($asset);
      ++$deleted;
    }

    if ($deleted > 0) {
      $this->entityManager->flush();
    }

    $this->logger->info('Garbage collected orphaned assets', ['count' => $deleted]);

    return $deleted;
  }

  public function hasExistingMappings(string $projectId): bool
  {
    return $this->mappingRepository->hasAnyForProject($projectId);
  }

  /**
   * Collect all asset files from the extracted project directory.
   * Supports both single-scene and multi-scene layouts.
   *
   * @return list<array{absolutePath: string, pathInZip: string}>
   */
  private function collectAssetFiles(string $extractDir): array
  {
    $files = [];
    $extractDir = rtrim($extractDir, '/').'/';

    // Single-scene: images/, sounds/
    foreach (self::ASSET_DIRECTORIES as $dir) {
      $dirPath = $extractDir.$dir;
      if (is_dir($dirPath)) {
        $this->addFilesFromDirectory($dirPath, $dir, $files);
      }
    }

    // Multi-scene: {SceneName}/images/, {SceneName}/sounds/
    $entries = scandir($extractDir);
    if (false === $entries) {
      return $files;
    }

    foreach ($entries as $entry) {
      if ('.' === $entry || '..' === $entry) {
        continue;
      }

      $entryPath = $extractDir.$entry;
      if (!is_dir($entryPath)) {
        continue;
      }

      if (in_array($entry, ['images', 'sounds'], true)) {
        continue;
      }

      foreach (self::ASSET_DIRECTORIES as $dir) {
        $sceneDirPath = $entryPath.'/'.$dir;
        if (is_dir($sceneDirPath)) {
          $this->addFilesFromDirectory($sceneDirPath, $entry.'/'.$dir, $files);
        }
      }
    }

    return $files;
  }

  /**
   * @param list<array{absolutePath: string, pathInZip: string}> $files
   */
  private function addFilesFromDirectory(string $dirPath, string $zipPrefix, array &$files): void
  {
    $entries = scandir($dirPath);
    if (false === $entries) {
      return;
    }

    foreach ($entries as $entry) {
      if ('.' === $entry || '..' === $entry) {
        continue;
      }

      $absolutePath = $dirPath.'/'.$entry;
      if (is_file($absolutePath)) {
        $files[] = [
          'absolutePath' => $absolutePath,
          'pathInZip' => $zipPrefix.'/'.$entry,
        ];
      }
    }
  }
}
