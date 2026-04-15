<?php

declare(strict_types=1);

namespace App\Storage;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Special\ExampleProject;
use App\DB\Entity\Project\Special\FeaturedProject;
use App\DB\Entity\User\Notifications\ProjectDeletedNotification;
use App\DB\Entity\User\Notifications\ProjectExpiringNotification;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ProjectFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class StorageLifecycleService
{
  private const int BATCH_SIZE = 100;

  public const int PROTECTED_DAYS = -1;
  public const int ACTIVE_DAYS = 365;
  public const int STANDARD_DAYS = 90;
  public const int SHORT_DAYS = 30;

  public const float DISK_WARN_THRESHOLD = 0.70;
  public const float DISK_PRESSURE_THRESHOLD = 0.85;
  public const float DISK_CRITICAL_THRESHOLD = 0.95;

  private const int ACTIVE_DOWNLOAD_MINIMUM = 10;
  private const int USER_ACTIVE_DAYS = 180;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ProjectFileRepository $file_repository,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Determines which retention tier a project belongs to.
   *
   * @return int days until deletion (-1 = protected/never)
   */
  public function getRetentionDays(Project $project): int
  {
    if ($this->isProtected($project)) {
      return self::PROTECTED_DAYS;
    }

    if ($this->isActive($project)) {
      return self::ACTIVE_DAYS;
    }

    if ($this->isStandard($project)) {
      return self::STANDARD_DAYS;
    }

    return self::SHORT_DAYS;
  }

  /**
   * Protected: featured, example, or admin-approved projects. Never deleted.
   */
  public function isProtected(Project $project): bool
  {
    if ($project->isStorageProtected()) {
      return true;
    }

    $projectId = $project->getId();
    if (null === $projectId) {
      return false;
    }

    $featured = $this->entity_manager->createQueryBuilder()
      ->select('COUNT(f.id)')
      ->from(FeaturedProject::class, 'f')
      ->where('f.program = :project')
      ->setParameter('project', $project)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    if ((int) $featured > 0) {
      return true;
    }

    $example = $this->entity_manager->createQueryBuilder()
      ->select('COUNT(e.id)')
      ->from(ExampleProject::class, 'e')
      ->where('e.program = :project')
      ->setParameter('project', $project)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return (int) $example > 0;
  }

  /**
   * Active: >= 10 downloads OR owner logged in within 180 days.
   */
  public function isActive(Project $project): bool
  {
    if ($project->getDownloads() >= self::ACTIVE_DOWNLOAD_MINIMUM) {
      return true;
    }

    $user = $project->getUser();
    if (null === $user) {
      return false;
    }

    $lastLogin = $user->getLastLogin();
    if (null === $lastLogin) {
      return false;
    }

    $cutoff = new \DateTime('-'.self::USER_ACTIVE_DAYS.' days');

    return $lastLogin >= $cutoff;
  }

  /**
   * Standard: visible project by a verified user.
   */
  public function isStandard(Project $project): bool
  {
    if (!$project->getVisible()) {
      return false;
    }

    if ($project->getAutoHidden()) {
      return false;
    }

    $user = $project->getUser();
    if (null === $user) {
      return false;
    }

    return $user->isVerified();
  }

  /**
   * Returns the current disk usage ratio for the storage partition (0.0 to 1.0).
   */
  public function getDiskUsageRatio(string $path): float
  {
    $total = @disk_total_space($path);
    $free = @disk_free_space($path);

    if (false === $total || false === $free || 0.0 === $total) {
      return 0.0;
    }

    return 1.0 - ($free / $total);
  }

  /**
   * Applies disk pressure multiplier to retention days.
   */
  public function applyDiskPressure(int $retention_days, float $disk_ratio): int
  {
    if (self::PROTECTED_DAYS === $retention_days) {
      return self::PROTECTED_DAYS;
    }

    if ($disk_ratio >= self::DISK_CRITICAL_THRESHOLD) {
      return (int) ceil($retention_days / 4);
    }

    if ($disk_ratio >= self::DISK_PRESSURE_THRESHOLD) {
      return (int) ceil($retention_days / 2);
    }

    return $retention_days;
  }

  /**
   * Returns true if uploads should be paused due to critical disk pressure.
   */
  public function shouldPauseUploads(float $disk_ratio): bool
  {
    return $disk_ratio >= self::DISK_CRITICAL_THRESHOLD;
  }

  /**
   * Finds and deletes expired projects in batches.
   *
   * @return array{checked: int, deleted: int, errors: int}
   */
  public function deleteExpiredProjects(bool $dry_run = false, string $storage_path = ''): array
  {
    $disk_ratio = '' !== $storage_path ? $this->getDiskUsageRatio($storage_path) : 0.0;

    if ($disk_ratio >= self::DISK_WARN_THRESHOLD) {
      $this->logger->warning('Storage lifecycle: disk usage at {ratio}%', [
        'ratio' => round($disk_ratio * 100.0, 1),
      ]);
    }

    $checked = 0;
    $deleted = 0;
    $errors = 0;
    $offset = 0;
    $now = new \DateTime();

    while (true) {
      $projects = $this->entity_manager->createQueryBuilder()
        ->select('p')
        ->from(Project::class, 'p')
        ->leftJoin('p.user', 'u')
        ->orderBy('p.uploaded_at', 'ASC')
        ->setFirstResult($offset)
        ->setMaxResults(self::BATCH_SIZE)
        ->getQuery()
        ->getResult()
      ;

      if ([] === $projects) {
        break;
      }

      /** @var Project $project */
      foreach ($projects as $project) {
        ++$checked;

        $retention_days = $this->getRetentionDays($project);
        $retention_days = $this->applyDiskPressure($retention_days, $disk_ratio);

        if (self::PROTECTED_DAYS === $retention_days) {
          continue;
        }

        $expiry = (clone $project->getUploadedAt())->modify('+'.$retention_days.' days');

        if ($expiry >= $now) {
          continue;
        }

        $projectId = $project->getId() ?? 'unknown';
        $projectName = $project->getName();

        if ($dry_run) {
          $this->logger->info('Storage lifecycle [DRY-RUN]: would delete project {id} "{name}" (tier: {days}d, uploaded: {uploaded})', [
            'id' => $projectId,
            'name' => $projectName,
            'days' => $retention_days,
            'uploaded' => $project->getUploadedAt()->format('Y-m-d'),
          ]);
          ++$deleted;

          continue;
        }

        try {
          $user = $project->getUser();
          if ($user instanceof User) {
            $notification = new ProjectDeletedNotification($user, $projectName);
            $this->entity_manager->persist($notification);
            $this->entity_manager->flush();
          }
          $this->hardDeleteProject($project);
          ++$deleted;
          $this->logger->info('Storage lifecycle: deleted project {id} "{name}"', [
            'id' => $projectId,
            'name' => $projectName,
          ]);
        } catch (\Throwable $e) {
          ++$errors;
          $this->logger->error('Storage lifecycle: failed to delete project {id}: {error}', [
            'id' => $projectId,
            'error' => $e->getMessage(),
          ]);
        }
      }

      $offset += self::BATCH_SIZE;
      $this->entity_manager->clear();
    }

    return ['checked' => $checked, 'deleted' => $deleted, 'errors' => $errors];
  }

  /**
   * Permanently deletes a project: files, screenshots, thumbnails, and database record.
   * Doctrine cascade handles related entities (comments, notifications, likes, etc.).
   */
  public function hardDeleteProject(Project $project): void
  {
    $projectId = $project->getId();
    if (null === $projectId) {
      throw new \InvalidArgumentException('Cannot hard-delete a project without an ID');
    }

    // Delete zip file
    $this->file_repository->deleteProjectZipFileIfExists($projectId);

    // Delete extracted files
    try {
      $this->file_repository->deleteProjectExtractFiles($projectId);
    } catch (\Exception $e) {
      $this->logger->warning('Storage lifecycle: could not delete extract files for project {id}: {error}', [
        'id' => $projectId,
        'error' => $e->getMessage(),
      ]);
    }

    // Delete screenshots and thumbnails
    $this->screenshot_repository->deleteScreenshot($projectId);
    $this->screenshot_repository->deleteThumbnail($projectId);

    // Remove from database (Doctrine cascades handle relations)
    $this->entity_manager->remove($project);
    $this->entity_manager->flush();
  }

  /**
   * Sends expiry warning notifications for projects that will be deleted within the given threshold.
   *
   * @return array{warned: int}
   */
  public function sendExpiryWarnings(int $warning_days = 7, string $storage_path = ''): array
  {
    $disk_ratio = '' !== $storage_path ? $this->getDiskUsageRatio($storage_path) : 0.0;
    $now = new \DateTime();
    $warned = 0;
    $offset = 0;

    while (true) {
      $projects = $this->entity_manager->createQueryBuilder()
        ->select('p')
        ->from(Project::class, 'p')
        ->leftJoin('p.user', 'u')
        ->orderBy('p.uploaded_at', 'ASC')
        ->setFirstResult($offset)
        ->setMaxResults(self::BATCH_SIZE)
        ->getQuery()
        ->getResult()
      ;

      if ([] === $projects) {
        break;
      }

      /** @var Project $project */
      foreach ($projects as $project) {
        $retention_days = $this->getRetentionDays($project);
        $retention_days = $this->applyDiskPressure($retention_days, $disk_ratio);

        if (self::PROTECTED_DAYS === $retention_days) {
          continue;
        }

        $expiry = (clone $project->getUploadedAt())->modify('+'.$retention_days.' days');
        $days_left = (int) $now->diff($expiry)->format('%r%a');

        if ($days_left <= 0 || $days_left > $warning_days) {
          continue;
        }

        $user = $project->getUser();
        if (!$user instanceof User) {
          continue;
        }

        // Check if we already sent a warning for this project recently
        $existing = $this->entity_manager->createQueryBuilder()
          ->select('COUNT(n.id)')
          ->from(ProjectExpiringNotification::class, 'n')
          ->where('n.user = :user')
          ->andWhere('n.program = :project')
          ->setParameter('user', $user)
          ->setParameter('project', $project)
          ->getQuery()
          ->getSingleScalarResult()
        ;

        if ((int) $existing > 0) {
          continue;
        }

        $notification = new ProjectExpiringNotification($user, $project, $days_left);
        $this->entity_manager->persist($notification);
        ++$warned;
      }

      $this->entity_manager->flush();
      $offset += self::BATCH_SIZE;
      $this->entity_manager->clear();
    }

    return ['warned' => $warned];
  }
}
