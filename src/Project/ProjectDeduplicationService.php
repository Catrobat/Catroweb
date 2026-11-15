<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Storage\ContentAddressableStore;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
   * Process a newly extracted project: hash all assets, store unique ones, create mappings.
   * An asset's references are its mappings; there is no counter to drift.
   */
  public function deduplicateProject(Project $project, string $extractDir): void
  {
    $assetFiles = [];
    foreach ($this->collectAssetFiles($extractDir) as $assetFile) {
      $assetFiles[] = [$this->store->hashFile($assetFile['absolutePath']), $assetFile];
    }

    if ([] === $assetFiles) {
      return;
    }

    // Sorted by hash, so concurrent uploads lock shared asset rows in the same order.
    usort($assetFiles, static fn (array $a, array $b): int => strcmp($a[0], $b[0]));

    // Plain SQL: a concurrent upload may insert the same hash, and a failed ORM flush would close the
    // EntityManager for the rest of the request.
    $this->entityManager->getConnection()->transactional(function (Connection $connection) use ($project, $assetFiles): void {
      foreach ($assetFiles as [$hash, $assetFile]) {
        $absolutePath = $assetFile['absolutePath'];
        // Locks the asset row until commit: garbage collection cannot delete it, or its file, before the mapping exists.
        $connection->executeStatement(
          'INSERT INTO project_asset (hash, size, mime_type, storage_path, created_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE hash = hash',
          [$hash, (int) filesize($absolutePath), mime_content_type($absolutePath) ?: 'application/octet-stream', $this->store->relativePath($hash), new \DateTimeImmutable()->format('Y-m-d H:i:s')],
        );
        $this->store->store($absolutePath, $hash);
        $connection->executeStatement(
          'INSERT INTO project_asset_mapping (project_id, asset_hash, original_filename, path_in_zip) VALUES (?, ?, ?, ?)',
          [$project->getId(), $hash, basename($assetFile['pathInZip']), $assetFile['pathInZip']],
        );
      }
    });

    $this->logger->info('Deduplicated project assets', [
      'project_id' => $project->getId(),
      'total_files' => count($assetFiles),
    ]);
  }

  public function removeProjectMappings(string $projectId): void
  {
    $this->entityManager->getConnection()->executeStatement('DELETE FROM project_asset_mapping WHERE project_id = ?', [$projectId]);
  }

  /**
   * Garbage-collect one batch of assets no project maps, in hash order after $after.
   * Assets younger than $created_before are skipped: an upload may still be mapping them.
   * Intended to be called from a background job, NOT inline during upload.
   *
   * @return array{count: int, bytes: int, last: string, more: bool} last: the last hash looked at; more: a full batch was listed
   */
  public function garbageCollectBatch(\DateTimeImmutable $created_before, int $batch_size, string $after = ''): array
  {
    $deleted = ['count' => 0, 'bytes' => 0];
    $orphans = $this->assetRepository->findOrphans($batch_size, $created_before, $after);
    foreach ($orphans as $hash => $size) {
      $after = (string) $hash;
      try {
        // The delete waits for an upload holding the row and fails on the foreign key once that upload mapped it.
        $this->entityManager->getConnection()->transactional(function (Connection $connection) use ($after, $size, &$deleted): void {
          if (1 === $connection->executeStatement('DELETE FROM project_asset WHERE hash = ?', [$after])) {
            $this->store->delete($after);
            ++$deleted['count'];
            $deleted['bytes'] += $size;
          }
        });
      } catch (ForeignKeyConstraintViolationException) {
      }
    }

    return $deleted + ['last' => $after, 'more' => count($orphans) === $batch_size];
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
