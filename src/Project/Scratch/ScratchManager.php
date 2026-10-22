<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\ProjectManager;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use App\User\UserManager;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Imports a public Scratch project as a real Pocket Code project: converted, then published through the normal
 * upload pipeline (malware scan, sanitizing, validation) under a shadow account of its Scratch author.
 */
class ScratchManager
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly UserManager $user_manager,
    private readonly ScratchFetcher $fetcher,
    private readonly ScratchToCatrobatConverter $converter,
    private readonly LoggerInterface $logger,
    private readonly Connection $connection,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  /**
   * @param callable(): void $before_conversion called only when a conversion is actually needed (rate limiting)
   *
   * @throws ScratchConversionException with a message that can be shown to the user
   */
  public function importProject(int $scratch_id, callable $before_conversion, ?string $ip): Project
  {
    /** @var Project|null $existing */
    $existing = $this->project_manager->findOneByScratchId($scratch_id);
    $converted = null !== $existing && $existing->getFilesize() > 0 ? $existing : null;

    try {
      $metadata = $this->fetcher->fetchMetadata($scratch_id);
    } catch (ScratchConversionException $exception) {
      if (null !== $converted) {
        return $converted; // Scratch is down or the project was unshared: keep serving our copy
      }

      throw $exception;
    }

    $modified = TimeUtils::dateTimeFromScratch((string) ($metadata['history']['modified'] ?? ''));
    if (null !== $converted && (!$modified instanceof \DateTime || $converted->getLastModifiedAt()->getTimestamp() >= $modified->getTimestamp())) {
      return $converted;
    }

    $before_conversion();
    set_time_limit(120);

    // ponytail: one conversion at a time per server; a job queue if people regularly see "busy".
    $lock = fopen(sys_get_temp_dir().'/catroweb-scratch-converter.lock', 'c');
    if (false === $lock) {
      throw new \RuntimeException('The Scratch converter lock file cannot be created.');
    }

    if (!flock($lock, LOCK_EX | LOCK_NB)) {
      fclose($lock);

      throw new ScratchConversionException('Another Scratch project is being converted right now. Please try again in a minute.');
    }

    $temp = (string) tempnam(sys_get_temp_dir(), 'scratch');
    $archive = $temp.'.catrobat';
    try {
      $user = $existing?->getUser() ?? $this->user_manager->createUserFromScratch($metadata['author'] ?? []);
      if (null === $user) {
        throw new ScratchConversionException('The Scratch author could not be resolved.');
      }

      // Uploads update the author's project of the same name; keep two Scratch projects with one title apart.
      $title = trim((string) ($metadata['title'] ?? ''));
      $namesake = '' === $title ? null : $this->project_manager->findOneByNameAndUser($title, $user);
      if (null !== $namesake && $namesake->getScratchId() !== $scratch_id) {
        $metadata['title'] = $title.' ('.$scratch_id.')';
      }

      $project_json = $this->fetcher->fetchProject($scratch_id, $metadata);
      $this->converter->convert($scratch_id, $project_json, $metadata, $this->fetcher->fetchAssets($project_json), $this->fetcher->fetchThumbnail($scratch_id), $archive);

      $project = $this->project_manager->addProject(new AddProjectRequest($user, new File($archive), $ip, null, Flavor::POCKETCODE, $existing?->getId()));
      if (null === $project) {
        throw new ScratchConversionException('The converted project could not be published.');
      }

      $project->setScratchId($scratch_id);
      $this->project_manager->save($project);

      return $project;
    } catch (\Throwable $throwable) {
      // Drop half-applied entity changes, so nothing flushed later in this request writes them.
      if ($this->entity_manager->isOpen()) {
        $this->entity_manager->clear();
      }

      if (null !== $existing) {
        $this->markOutdated((string) $existing->getId());
      }

      throw $this->userFacing($scratch_id, $throwable);
    } finally {
      @unlink($archive);
      @unlink($temp);
      flock($lock, LOCK_UN);
      fclose($lock);
    }
  }

  /**
   * A failed update may already have stored the new modification time; reset it so the next open converts again
   * instead of serving the old archive as current. Plain SQL: the EntityManager may be closed by now.
   */
  private function markOutdated(string $project_id): void
  {
    try {
      $this->connection->executeStatement('UPDATE program SET last_modified_at = :epoch WHERE id = :id', ['epoch' => '1970-01-01 00:00:00', 'id' => $project_id]);
    } catch (\Throwable $throwable) {
      $this->logger->error('Could not mark a converted Scratch project as outdated', ['project' => $project_id, 'exception' => $throwable]);
    }
  }

  private function userFacing(int $scratch_id, \Throwable $throwable): ScratchConversionException
  {
    if ($throwable instanceof ScratchConversionException) {
      return $throwable;
    }

    if ($throwable instanceof InvalidCatrobatFileException) {
      $this->logger->warning('Converted Scratch project rejected by upload validation', ['scratch_id' => $scratch_id, 'error' => $throwable->getMessage()]);

      return new ScratchConversionException('The converted project did not pass the upload checks.', 0, $throwable);
    }

    $this->logger->error('Scratch conversion failed', ['scratch_id' => $scratch_id, 'exception' => $throwable]);

    return new ScratchConversionException('This Scratch project could not be converted.', 0, $throwable);
  }
}
