<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\System\BackgroundJob;
use App\DB\EntityRepository\System\BackgroundJobRepository;
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
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Imports a public Scratch project as a real Pocket Code project: converted, then published through the normal
 * upload pipeline (malware scan, sanitizing, validation) under a shadow account of its Scratch author.
 */
class ScratchManager implements ResetInterface
{
  final public const string JOB_TYPE = 'scratch_import';

  /** @var array<int, array<string, mixed>> Scratch metadata fetched in this request or job, so a check and its import ask once */
  private array $metadata = [];

  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly UserManager $user_manager,
    private readonly ScratchFetcher $fetcher,
    private readonly ScratchToCatrobatConverter $converter,
    private readonly LoggerInterface $logger,
    private readonly Connection $connection,
    private readonly EntityManagerInterface $entity_manager,
    private readonly BackgroundJobRepository $job_repository,
    private readonly MessageBusInterface $message_bus,
  ) {
  }

  /**
   * Our copy if it is up to date (or Scratch can't be asked and a copy exists), null if it must be converted.
   *
   * @throws ScratchConversionException with a message that can be shown to the user
   */
  public function currentCopy(int $scratch_id): ?Project
  {
    $existing = $this->project_manager->findOneByScratchId($scratch_id);
    $converted = null !== $existing && $existing->getFilesize() > 0 ? $existing : null;

    try {
      $metadata = $this->metadata($scratch_id);
    } catch (ScratchConversionException $exception) {
      if (null !== $converted) {
        return $converted; // Scratch is down or the project was unshared: keep serving our copy
      }

      throw $exception;
    }

    $modified = TimeUtils::dateTimeFromScratch((string) ($metadata['history']['modified'] ?? ''));
    $current = null !== $converted && ($converted->getScratchConverterVersion() ?? 1) >= ScratchToCatrobatConverter::VERSION;
    if ($current && (!$modified instanceof \DateTime || $converted->getLastModifiedAt()->getTimestamp() >= $modified->getTimestamp())) {
      return $converted;
    }

    return null;
  }

  /**
   * Queues the import for the scratch worker. A request for a project that is already queued joins that job, without
   * using quota again: it converts once either way.
   *
   * @param callable(): void $before_conversion called only when a new job is queued (rate limiting)
   */
  public function queueImport(int $scratch_id, callable $before_conversion, ?string $ip): BackgroundJob
  {
    $job = $this->job_repository->findUnfinished(self::JOB_TYPE, (string) $scratch_id);
    if (null !== $job) {
      return $job;
    }

    $before_conversion();
    $job = new BackgroundJob(self::JOB_TYPE, (string) $scratch_id);
    $this->entity_manager->persist($job);
    $this->entity_manager->flush();
    $this->message_bus->dispatch(new ConvertScratchProject($job->getId(), $scratch_id, $ip));

    return $job;
  }

  /**
   * @param callable(): void $before_conversion called only when a conversion is actually needed (rate limiting)
   *
   * @throws ScratchConversionException with a message that can be shown to the user
   */
  public function importProject(int $scratch_id, callable $before_conversion, ?string $ip): Project
  {
    $current = $this->currentCopy($scratch_id);
    if (null !== $current) {
      return $current;
    }

    $existing = $this->project_manager->findOneByScratchId($scratch_id);
    $metadata = $this->metadata($scratch_id);

    $before_conversion();
    set_time_limit(120);

    // ponytail: one conversion at a time per server; a job queue if people regularly see "busy".
    $lock = fopen(sys_get_temp_dir().'/catroweb-scratch-converter.lock', 'c');
    if (false === $lock) {
      throw new \RuntimeException('The Scratch converter lock file cannot be created.');
    }

    if (!flock($lock, LOCK_EX | LOCK_NB)) {
      fclose($lock);

      throw new ScratchConversionException('Another Scratch project is being converted right now. Please try again in a minute.', ScratchConversionException::BUSY);
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

      $project = $this->project_manager->addProject(new AddProjectRequest($user, new File($archive), $ip, null, Flavor::POCKETCODE, $existing?->getId(), $scratch_id))
        ?? throw new ScratchConversionException('The converted project could not be published.');
      $project->setScratchConverterVersion(ScratchToCatrobatConverter::VERSION);
      $this->entity_manager->flush();

      return $project;
    } catch (\Throwable $throwable) {
      // Drop half-applied entity changes, so nothing flushed later in this request writes them.
      if ($this->entity_manager->isOpen()) {
        $this->entity_manager->clear();
      }

      $this->markOutdated($scratch_id);

      throw $this->userFacing($scratch_id, $throwable);
    } finally {
      @unlink($archive);
      @unlink($temp);
      flock($lock, LOCK_UN);
      fclose($lock);
    }
  }

  /**
   * A failed import may already have stored a project with a current modification time: an update of the existing
   * one, or a new row that addProject persisted before failing. Reset it so the next open converts again instead
   * of serving it as current. By scratch_id, which addProject sets before the first persist, so it finds both.
   * Plain SQL: the EntityManager may be closed by now.
   */
  private function markOutdated(int $scratch_id): void
  {
    try {
      $this->connection->executeStatement('UPDATE program SET last_modified_at = :epoch WHERE scratch_id = :scratch_id', ['epoch' => '1970-01-01 00:00:00', 'scratch_id' => $scratch_id]);
    } catch (\Throwable $throwable) {
      $this->logger->error('Could not mark a converted Scratch project as outdated', ['scratch_id' => $scratch_id, 'exception' => $throwable]);
    }
  }

  /** Workers handle many jobs in one process: forget the metadata between them. */
  #[\Override]
  public function reset(): void
  {
    $this->metadata = [];
  }

  /** @return array<string, mixed> */
  private function metadata(int $scratch_id): array
  {
    return $this->metadata[$scratch_id] ??= $this->fetcher->fetchMetadata($scratch_id);
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

    return new ScratchConversionException('This Scratch project could not be converted.', ScratchConversionException::INTERNAL, $throwable);
  }
}
